<?php
/**
 * Seat Change Request Model
 * Handles seat change requests from organisation admins to super admins
 */

class SeatChangeRequest {
    
    /**
     * Create a new seat change request
     */
    public static function create($organisationId, $requestedByUserId, $currentSeats, $requestedSeats, $message = null) {
        $db = getDbConnection();
        $stmt = $db->prepare("
            INSERT INTO seat_change_requests (organisation_id, requested_by_user_id, current_seats, requested_seats, message)
            VALUES (?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $organisationId,
            $requestedByUserId,
            $currentSeats,
            $requestedSeats,
            $message
        ]);
    }
    
    /**
     * Find request by ID
     */
    public static function findById($id) {
        $db = getDbConnection();
        $stmt = $db->prepare("
            SELECT scr.*, 
                   o.name as organisation_name, o.domain as organisation_domain,
                   u1.first_name as requester_first_name, u1.last_name as requester_last_name, u1.email as requester_email,
                   u2.first_name as reviewer_first_name, u2.last_name as reviewer_last_name
            FROM seat_change_requests scr
            JOIN organisations o ON scr.organisation_id = o.id
            JOIN users u1 ON scr.requested_by_user_id = u1.id
            LEFT JOIN users u2 ON scr.reviewed_by_user_id = u2.id
            WHERE scr.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Get all pending requests
     */
    public static function getPendingRequests() {
        $db = getDbConnection();
        $stmt = $db->prepare("
            SELECT scr.*, 
                   o.name as organisation_name, o.domain as organisation_domain,
                   u.first_name as requester_first_name, u.last_name as requester_last_name, u.email as requester_email
            FROM seat_change_requests scr
            JOIN organisations o ON scr.organisation_id = o.id
            JOIN users u ON scr.requested_by_user_id = u.id
            WHERE scr.status = 'pending'
            ORDER BY scr.created_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Get requests for an organisation
     */
    public static function getByOrganisation($organisationId) {
        $db = getDbConnection();
        $stmt = $db->prepare("
            SELECT scr.*, 
                   u1.first_name as requester_first_name, u1.last_name as requester_last_name,
                   u2.first_name as reviewer_first_name, u2.last_name as reviewer_last_name
            FROM seat_change_requests scr
            JOIN users u1 ON scr.requested_by_user_id = u1.id
            LEFT JOIN users u2 ON scr.reviewed_by_user_id = u2.id
            WHERE scr.organisation_id = ?
            ORDER BY scr.created_at DESC
        ");
        $stmt->execute([$organisationId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Approve a request
     */
    public static function approve($id, $reviewedByUserId, $reviewNotes = null) {
        $db = getDbConnection();
        $request = self::findById($id);
        
        if (!$request) {
            throw new Exception('Request not found.');
        }
        
        $db->beginTransaction();
        try {
            // Update request status
            $stmt = $db->prepare("
                UPDATE seat_change_requests 
                SET status = 'approved',
                    reviewed_by_user_id = ?,
                    review_notes = ?,
                    reviewed_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$reviewedByUserId, $reviewNotes, $id]);
            
            // Update organisation seats
            $stmt = $db->prepare("UPDATE organisations SET seats_allocated = ? WHERE id = ?");
            $stmt->execute([$request['requested_seats'], $request['organisation_id']]);
            
            // Mark as completed
            $stmt = $db->prepare("UPDATE seat_change_requests SET status = 'completed' WHERE id = ?");
            $stmt->execute([$id]);
            
            $db->commit();
            return true;
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }
    
    /**
     * Reject a request
     */
    public static function reject($id, $reviewedByUserId, $reviewNotes = null) {
        $db = getDbConnection();
        $stmt = $db->prepare("
            UPDATE seat_change_requests 
            SET status = 'rejected',
                reviewed_by_user_id = ?,
                review_notes = ?,
                reviewed_at = NOW()
            WHERE id = ?
        ");
        return $stmt->execute([$reviewedByUserId, $reviewNotes, $id]);
    }
    
    /**
     * Get count of pending requests
     */
    public static function getPendingCount() {
        $db = getDbConnection();
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM seat_change_requests WHERE status = 'pending'");
        $stmt->execute();
        $result = $stmt->fetch();
        return $result ? $result['count'] : 0;
    }
}

