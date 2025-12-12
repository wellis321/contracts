-- Migration: Fix seat counting to only count verified and active users
-- This recalculates seats_used for all organisations based on verified and active users only

-- Recalculate seats_used for all organisations
UPDATE organisations o
SET o.seats_used = (
    SELECT COUNT(*) 
    FROM users u
    WHERE u.organisation_id = o.id 
    AND u.email_verified = TRUE 
    AND u.is_active = TRUE
);

-- Add a note about seat counting logic
-- Seats are now only counted when:
-- 1. User's email is verified (email_verified = TRUE)
-- 2. User account is active (is_active = TRUE)
-- 
-- This means:
-- - Unverified accounts don't take up seats
-- - Suspended accounts don't take up seats
-- - Seats are allocated when email is verified, not on registration

