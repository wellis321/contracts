<?php
/**
 * Team Model
 * Handles hierarchical team structure (Teams → Areas → Regions)
 */
class Team {
    
    /**
     * Get all teams for an organisation
     */
    public static function findByOrganisation($organisationId, $includeInactive = false) {
        $db = getDbConnection();
        $sql = "
            SELECT t.*, tt.name as team_type_name, tt.display_order as team_type_order
            FROM teams t
            LEFT JOIN team_types tt ON t.team_type_id = tt.id
            WHERE t.organisation_id = ?
        ";
        if (!$includeInactive) {
            $sql .= " AND t.is_active = 1";
        }
        $sql .= " ORDER BY tt.display_order, tt.name, t.name";
        $stmt = $db->prepare($sql);
        $stmt->execute([$organisationId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get team by ID
     */
    public static function findById($id) {
        $db = getDbConnection();
        $stmt = $db->prepare("
            SELECT t.*, tt.name as team_type_name
            FROM teams t
            LEFT JOIN team_types tt ON t.team_type_id = tt.id
            WHERE t.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Get all child teams (recursive - gets all descendants)
     */
    public static function getChildTeams($teamId, $includeSelf = false) {
        $db = getDbConnection();
        $teams = [];
        
        if ($includeSelf) {
            $team = self::findById($teamId);
            if ($team) {
                $teams[] = $team['id'];
            }
        }
        
        // Get direct children
        $stmt = $db->prepare("SELECT id FROM teams WHERE parent_team_id = ? AND is_active = 1");
        $stmt->execute([$teamId]);
        $children = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        foreach ($children as $childId) {
            $teams[] = $childId;
            // Recursively get grandchildren
            $grandchildren = self::getChildTeams($childId, false);
            $teams = array_merge($teams, $grandchildren);
        }
        
        return array_unique($teams);
    }
    
    /**
     * Get all parent teams (gets the full hierarchy path)
     */
    public static function getParentTeams($teamId) {
        $db = getDbConnection();
        $parents = [];
        $currentTeam = self::findById($teamId);
        
        while ($currentTeam && $currentTeam['parent_team_id']) {
            $parent = self::findById($currentTeam['parent_team_id']);
            if ($parent) {
                $parents[] = $parent;
                $currentTeam = $parent;
            } else {
                break;
            }
        }
        
        return array_reverse($parents); // Return from top to bottom
    }
    
    /**
     * Get full team hierarchy path (e.g., "Region > Area > Team")
     */
    public static function getHierarchyPath($teamId) {
        $parents = self::getParentTeams($teamId);
        $team = self::findById($teamId);
        
        $path = [];
        foreach ($parents as $parent) {
            $path[] = $parent['name'];
        }
        if ($team) {
            $path[] = $team['name'];
        }
        
        return implode(' > ', $path);
    }
    
    /**
     * Get team type name for a team
     */
    public static function getTeamTypeName($teamId) {
        $team = self::findById($teamId);
        return $team ? ($team['team_type_name'] ?? 'Team') : 'Team';
    }
    
    /**
     * Create team
     */
    public static function create($organisationId, $name, $teamTypeId = null, $parentTeamId = null, $description = null) {
        $db = getDbConnection();
        $stmt = $db->prepare("
            INSERT INTO teams (organisation_id, parent_team_id, team_type_id, name, description)
            VALUES (?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $organisationId,
            $parentTeamId,
            $teamTypeId,
            $name,
            $description
        ]);
    }
    
    /**
     * Update team
     */
    public static function update($id, $data) {
        $db = getDbConnection();
        $stmt = $db->prepare("
            UPDATE teams SET
                name = ?,
                parent_team_id = ?,
                team_type_id = ?,
                description = ?,
                is_active = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['name'],
            $data['parent_team_id'] ?? null,
            $data['team_type_id'] ?? null,
            $data['description'] ?? null,
            $data['is_active'] ?? true,
            $id
        ]);
    }
    
    /**
     * Get teams user belongs to
     */
    public static function getUserTeams($userId) {
        $db = getDbConnection();
        $stmt = $db->prepare("
            SELECT t.*, ut.team_role_id, ut.is_primary, tr.name as role_name, tr.access_level
            FROM user_teams ut
            JOIN teams t ON ut.team_id = t.id
            LEFT JOIN team_roles tr ON ut.team_role_id = tr.id
            WHERE ut.user_id = ? AND t.is_active = 1
            ORDER BY ut.is_primary DESC, t.name
        ");
        $stmt->execute([$userId]);
        $results = $stmt->fetchAll();
        // Backward compatibility: add role_in_team field
        foreach ($results as &$result) {
            $result['role_in_team'] = $result['role_name'] ?? 'member';
        }
        return $results;
    }
    
    /**
     * Get user's role in a specific team
     */
    public static function getUserRoleInTeam($userId, $teamId) {
        $db = getDbConnection();
        $stmt = $db->prepare("
            SELECT role_in_team FROM user_teams
            WHERE user_id = ? AND team_id = ?
        ");
        $stmt->execute([$userId, $teamId]);
        $result = $stmt->fetch();
        return $result ? $result['role_in_team'] : null;
    }
    
    /**
     * Check if user has access to team (directly or through parent)
     */
    public static function userHasAccessToTeam($userId, $teamId) {
        $userTeams = self::getUserTeams($userId);
        $userTeamIds = array_column($userTeams, 'id');
        
        // Check direct access
        if (in_array($teamId, $userTeamIds)) {
            return true;
        }
        
        // Check if user's team is a parent of the target team
        foreach ($userTeamIds as $userTeamId) {
            $childTeams = self::getChildTeams($userTeamId, true);
            if (in_array($teamId, $childTeams)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get all team IDs user can access (including child teams)
     */
    public static function getAccessibleTeamIds($userId, $organisationId) {
        $userTeams = self::getUserTeams($userId);
        $accessibleIds = [];
        $db = getDbConnection();
        
        foreach ($userTeams as $team) {
            $accessLevel = $team['access_level'] ?? null;
            $roleName = $team['role_in_team'] ?? '';
            
            // Check custom role access level first
            if ($accessLevel === 'organisation') {
                // Organisation-level access can see all teams
                $stmt = $db->prepare("SELECT id FROM teams WHERE organisation_id = ? AND is_active = 1");
                $stmt->execute([$organisationId]);
                return $stmt->fetchAll(PDO::FETCH_COLUMN);
            }
            
            // Team-level access or backward compatibility
            if ($accessLevel === 'team' || $roleName === 'manager' || $roleName === 'admin') {
                $childTeams = self::getChildTeams($team['id'], true);
                $accessibleIds = array_merge($accessibleIds, $childTeams);
            }
            
            // Backward compatibility: finance and senior managers can access all teams
            if ($roleName === 'finance' || $roleName === 'senior_manager') {
                $stmt = $db->prepare("SELECT id FROM teams WHERE organisation_id = ? AND is_active = 1");
                $stmt->execute([$organisationId]);
                return $stmt->fetchAll(PDO::FETCH_COLUMN);
            }
        }
        
        return array_unique($accessibleIds);
    }
    
    /**
     * Assign user to team
     */
    public static function assignUserToTeam($userId, $teamId, $teamRoleId, $isPrimary = false) {
        $db = getDbConnection();
        $stmt = $db->prepare("
            INSERT INTO user_teams (user_id, team_id, team_role_id, is_primary)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                team_role_id = VALUES(team_role_id),
                is_primary = VALUES(is_primary)
        ");
        return $stmt->execute([$userId, $teamId, $teamRoleId, $isPrimary ? 1 : 0]);
    }
    
    /**
     * Remove user from team
     */
    public static function removeUserFromTeam($userId, $teamId) {
        $db = getDbConnection();
        $stmt = $db->prepare("DELETE FROM user_teams WHERE user_id = ? AND team_id = ?");
        return $stmt->execute([$userId, $teamId]);
    }
    
    /**
     * Get team tree structure (for display)
     */
    public static function getTeamTree($organisationId) {
        $allTeams = self::findByOrganisation($organisationId, true);
        $tree = [];
        
        // Build tree structure
        foreach ($allTeams as $team) {
            if (!$team['parent_team_id']) {
                // Root level
                $tree[$team['id']] = [
                    'team' => $team,
                    'children' => self::buildTeamTreeRecursive($allTeams, $team['id'])
                ];
            }
        }
        
        return $tree;
    }
    
    /**
     * Recursive helper to build team tree
     */
    private static function buildTeamTreeRecursive($allTeams, $parentId) {
        $children = [];
        foreach ($allTeams as $team) {
            if ($team['parent_team_id'] == $parentId) {
                $children[$team['id']] = [
                    'team' => $team,
                    'children' => self::buildTeamTreeRecursive($allTeams, $team['id'])
                ];
            }
        }
        return $children;
    }
}

