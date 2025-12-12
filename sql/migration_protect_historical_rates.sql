-- Migration: Protect Historical Rates from Modification
-- Prevents updates to rates where is_current = 0 (historical rates)
-- Only allows updating is_current flag from 1 to 0 (when superseding with new rate)

-- Drop trigger if it exists (for re-running migration)
DROP TRIGGER IF EXISTS prevent_historical_rate_updates;

DELIMITER $$

-- Trigger to prevent updates to historical rates
CREATE TRIGGER prevent_historical_rate_updates
BEFORE UPDATE ON rates
FOR EACH ROW
BEGIN
    -- Prevent any updates to non-current rates (historical rates)
    IF OLD.is_current = 0 THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Cannot modify historical rates. Historical rates (is_current = 0) are immutable. Create a new rate instead.';
    -- When marking a rate as not current (1 -> 0), only allow changing is_current flag
    -- Prevent changing rate_amount or effective_from at the same time
    ELSEIF OLD.is_current = 1 AND NEW.is_current = 0 THEN
        -- Only allow changing is_current flag, preserve all other fields
        IF OLD.rate_amount != NEW.rate_amount OR OLD.effective_from != NEW.effective_from OR 
           OLD.contract_type_id != NEW.contract_type_id OR OLD.local_authority_id != NEW.local_authority_id THEN
            SIGNAL SQLSTATE '45000' 
            SET MESSAGE_TEXT = 'Cannot modify rate details when marking rate as not current. Only is_current flag can be changed from 1 to 0.';
        END IF;
    -- Allow updates to current rates (is_current = 1) - though setRate creates new rates instead
    END IF;
END$$

DELIMITER ;

