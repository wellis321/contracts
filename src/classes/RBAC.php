<?php
/**
 * Role-Based Access Control Class
 * Handles permissions and role checking
 */

class RBAC {
    
    /**
     * Check if user has a specific role
     */
    public static function hasRole($roleName) {
        if (!Auth::isLoggedIn()) {
            return false;
        }
        
        $userId = Auth::getUserId();
        $db = getDbConnection();
        
        $stmt = $db->prepare("
            SELECT r.name 
            FROM user_roles ur
            JOIN roles r ON ur.role_id = r.id
            WHERE ur.user_id = ? AND r.name = ?
        ");
        $stmt->execute([$userId, $roleName]);
        
        return $stmt->fetch() !== false;
    }
    
    /**
     * Check if user is superadmin
     */
    public static function isSuperAdmin() {
        return self::hasRole('superadmin');
    }
    
    /**
     * Check if user is organisation admin
     */
    public static function isOrganisationAdmin() {
        return self::hasRole('organisation_admin');
    }
    
    /**
     * Check if user is staff
     */
    public static function isStaff() {
        return self::hasRole('staff');
    }
    
    /**
     * Check if user is admin (either superadmin or organisation admin)
     */
    public static function isAdmin() {
        return self::isSuperAdmin() || self::isOrganisationAdmin();
    }
    
    /**
     * Get all roles for current user
     */
    public static function getUserRoles() {
        if (!Auth::isLoggedIn()) {
            return [];
        }
        
        $userId = Auth::getUserId();
        $db = getDbConnection();
        
        $stmt = $db->prepare("
            SELECT r.name 
            FROM user_roles ur
            JOIN roles r ON ur.role_id = r.id
            WHERE ur.user_id = ?
        ");
        $stmt->execute([$userId]);
        
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    /**
     * Helper to get redirect URL
     */
    private static function getRedirectUrl($path) {
        if (function_exists('url')) {
            return url($path);
        }
        $baseUrl = function_exists('getBaseUrl') ? getBaseUrl() : '';
        return ($baseUrl ?: '') . '/' . ltrim($path, '/');
    }
    
    /**
     * Require specific role - redirect if user doesn't have it
     */
    public static function requireRole($roleName) {
        Auth::requireLogin();
        
        if (!self::hasRole($roleName)) {
            header('Location: ' . self::getRedirectUrl('index.php?error=access_denied'));
            exit;
        }
    }
    
    /**
     * Require admin access
     */
    public static function requireAdmin() {
        Auth::requireLogin();
        
        if (!self::isAdmin()) {
            header('Location: ' . self::getRedirectUrl('index.php?error=access_denied'));
            exit;
        }
    }
    
    /**
     * Require superadmin access
     */
    public static function requireSuperAdmin() {
        Auth::requireLogin();
        
        if (!self::isSuperAdmin()) {
            header('Location: ' . self::getRedirectUrl('index.php?error=access_denied'));
            exit;
        }
    }
    
    /**
     * Require organisation admin access (not superadmin)
     */
    public static function requireOrganisationAdmin() {
        Auth::requireLogin();
        
        if (!self::isOrganisationAdmin()) {
            // If superadmin, redirect to superadmin panel
            if (self::isSuperAdmin()) {
                header('Location: ' . self::getRedirectUrl('superadmin.php'));
            } else {
                header('Location: ' . self::getRedirectUrl('index.php?error=access_denied'));
            }
            exit;
        }
    }
    
    /**
     * Check if user can access organisation data
     */
    public static function canAccessOrganisation($organisationId) {
        if (self::isSuperAdmin()) {
            return true; // Superadmin can access all
        }
        
        $userOrgId = Auth::getOrganisationId();
        return $userOrgId == $organisationId;
    }
    
    /**
     * Check if user can access a contract (team-based access control)
     */
    public static function canAccessContract($contractId) {
        if (self::isSuperAdmin() || self::isOrganisationAdmin()) {
            // Superadmins and organisation admins can access all contracts in their organisation
            $organisationId = Auth::getOrganisationId();
            $db = getDbConnection();
            $stmt = $db->prepare("SELECT id FROM contracts WHERE id = ? AND organisation_id = ?");
            $stmt->execute([$contractId, $organisationId]);
            return $stmt->fetch() !== false;
        }
        
        $userId = Auth::getUserId();
        $organisationId = Auth::getOrganisationId();
        
        // Get user's teams and roles
        $userTeams = Team::getUserTeams($userId);
        
        // Check if user has organisation-level access (can access all contracts)
        foreach ($userTeams as $team) {
            $accessLevel = $team['access_level'] ?? null;
            $roleName = $team['role_in_team'] ?? '';
            
            // Check custom role access level or backward compatibility
            if ($accessLevel === 'organisation' || $roleName === 'finance' || $roleName === 'senior_manager') {
                // Organisation-level roles can access all contracts in organisation
                $db = getDbConnection();
                $stmt = $db->prepare("SELECT id FROM contracts WHERE id = ? AND organisation_id = ?");
                $stmt->execute([$contractId, $organisationId]);
                return $stmt->fetch() !== false;
            }
        }
        
        // Get contract's team
        $db = getDbConnection();
        $stmt = $db->prepare("SELECT team_id FROM contracts WHERE id = ? AND organisation_id = ?");
        $stmt->execute([$contractId, $organisationId]);
        $contract = $stmt->fetch();
        
        if (!$contract || !$contract['team_id']) {
            // Contract has no team assigned - only finance/senior managers can access
            return false;
        }
        
        // Check if user has access to the contract's team
        return Team::userHasAccessToTeam($userId, $contract['team_id']);
    }
    
    /**
     * Get accessible team IDs for current user (for filtering contracts)
     */
    public static function getAccessibleTeamIds() {
        if (self::isSuperAdmin() || self::isOrganisationAdmin()) {
            return null; // null means all teams (superadmin and org admin can see all)
        }
        
        $userId = Auth::getUserId();
        $organisationId = Auth::getOrganisationId();
        
        return Team::getAccessibleTeamIds($userId, $organisationId);
    }
    
    /**
     * Check if user can manage contracts (create/edit/delete)
     */
    public static function canManageContracts() {
        if (!Auth::isLoggedIn()) {
            return false;
        }
        
        // Superadmins and organisation admins can always manage
        if (self::isSuperAdmin() || self::isOrganisationAdmin()) {
            return true;
        }
        
        // Check if user has team-level or organisation-level access
        $userId = Auth::getUserId();
        $userTeams = Team::getUserTeams($userId);
        
        foreach ($userTeams as $team) {
            $accessLevel = $team['access_level'] ?? null;
            $roleName = $team['role_in_team'] ?? '';
            
            // Custom roles with team or organisation access can manage
            if ($accessLevel === 'team' || $accessLevel === 'organisation') {
                return true;
            }
            
            // Backward compatibility with old role names
            if (in_array($roleName, ['manager', 'admin', 'finance', 'senior_manager'])) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Require organisation access
     */
    public static function requireOrganisationAccess($organisationId) {
        Auth::requireLogin();
        
        if (!self::canAccessOrganisation($organisationId)) {
            header('Location: ' . self::getRedirectUrl('index.php?error=access_denied'));
            exit;
        }
    }
}
