# Audit Logging and Approval Workflow System

## Overview

This system provides comprehensive audit logging and configurable approval workflows for all changes across the application. Every change is tracked with full details, and organizations can configure who needs to approve different types of changes.

## Features

### 1. Comprehensive Audit Logging
- Tracks all create, update, and delete operations
- Records who made the change, when, and from where
- Stores old and new values for updates
- Captures IP address, user agent, and request URL
- JSON storage for complex change sets

### 2. Configurable Approval Workflows
- Organizations can set approval requirements per entity type
- Support for self-approval (default), manager approval, role-based approval
- Field-specific approval rules (e.g., require approval for `total_amount` changes)
- Priority-based rule evaluation
- Active/inactive rule toggling

### 3. Approval Request Management
- Tracks pending approvals
- Role-based and manager-based approver assignment
- Approval/rejection with reasons
- Expiration dates for approval requests

## Database Schema

### Tables Created

1. **audit_logs** - Stores all change history
2. **approval_rules** - Configuration for approval requirements
3. **approval_requests** - Tracks pending approvals

Run the migration:
```sql
source sql/migration_audit_logs.sql
```

## Integration Guide

### Adding Audit Logging to Models

The `Contract` model has been updated as an example. To add audit logging to other models:

#### 1. In Create Methods

```php
public static function create($data) {
    $db = getDbConnection();
    // ... your create logic ...
    $entityId = $db->lastInsertId();
    
    // Log the creation
    if ($entityId) {
        AuditService::logCreate('entity_type', $entityId, $data);
    }
    
    return $entityId;
}
```

#### 2. In Update Methods

```php
public static function update($id, $data) {
    $db = getDbConnection();
    
    // Get old data BEFORE updating
    $oldData = self::findById($id);
    
    // ... your update logic ...
    $result = $stmt->execute([...]);
    
    // Log the update
    if ($result && $oldData) {
        AuditService::logUpdate('entity_type', $id, $oldData, $data);
    }
    
    return $result;
}
```

#### 3. In Delete Methods

```php
public static function delete($id) {
    $db = getDbConnection();
    
    // Get data BEFORE deletion
    $oldData = self::findById($id);
    
    $stmt = $db->prepare("DELETE FROM table_name WHERE id = ?");
    $result = $stmt->execute([$id]);
    
    // Log the deletion
    if ($result && $oldData) {
        AuditService::logDelete('entity_type', $id, $oldData);
    }
    
    return $result;
}
```

### Entity Types

Use consistent entity type names:
- `contract`
- `rate`
- `person`
- `payment`
- `tender_application`
- `contract_type`
- `team`

## Using the System

### Viewing Audit Logs

Navigate to `/audit-logs.php` (admin only). You can:
- Filter by entity type, action, user, date range
- Search across all log fields
- View detailed change information
- See approval status for each change

### Configuring Approval Rules

Navigate to `/approval-rules.php` (admin only). You can:
- Create rules for specific entity types and actions
- Set approval requirements (self, manager, role-based)
- Configure field-specific rules
- Set rule priorities
- Activate/deactivate rules

### Example Approval Rules

1. **Require manager approval for contract deletions:**
   - Entity Type: `contract`
   - Action: `delete`
   - Approval Type: `manager`
   - Manager Level: `1`

2. **Require role approval for rate changes:**
   - Entity Type: `rate`
   - Action: `update`
   - Approval Type: `role`
   - Required Role: `organisation_admin`

3. **Require approval for contract value changes:**
   - Entity Type: `contract`
   - Action: `update`
   - Field Name: `total_amount`
   - Approval Type: `manager`
   - Manager Level: `1`

## Approval Workflow

1. User makes a change (create/update/delete)
2. System logs the change in `audit_logs`
3. System checks `approval_rules` for applicable rules
4. If approval required:
   - Creates `approval_request` entry
   - Sets audit log status to `pending`
   - Notifies approver (future: email notifications)
5. Approver reviews and approves/rejects
6. System updates audit log and approval request
7. Change is finalized (or rolled back if rejected)

## API Reference

### AuditService

- `logCreate($entityType, $entityId, $data, $metadata = [])` - Log creation
- `logUpdate($entityType, $entityId, $oldData, $newData, $fieldName = null, $metadata = [])` - Log update
- `logDelete($entityType, $entityId, $data, $metadata = [])` - Log deletion
- `checkAndRequestApproval($entityType, $action, $entityId, $auditLogId, $fieldName = null)` - Check and create approval request

### AuditLog

- `findByEntity($entityType, $entityId, $limit = 100)` - Get logs for specific entity
- `findByOrganisation($organisationId, $filters = [], $limit = 100, $offset = 0)` - Get logs with filtering
- `countByOrganisation($organisationId, $filters = [])` - Count logs

### ApprovalRule

- `getRulesForAction($entityType, $action, $fieldName = null)` - Get applicable rules
- `requiresApproval($entityType, $action, $fieldName = null)` - Check if approval needed
- `create($data)` - Create new rule
- `update($id, $data)` - Update rule
- `delete($id)` - Delete rule

### ApprovalRequest

- `getPendingForUser($userId)` - Get user's pending approvals
- `getPendingForOrganisation($organisationId)` - Get all pending approvals
- `approve($requestId, $rejectionReason = null)` - Approve a request
- `reject($requestId, $rejectionReason)` - Reject a request

## Future Enhancements

- Email notifications for approval requests
- Manager hierarchy lookup (currently placeholder)
- Custom approval conditions
- Bulk approval operations
- Approval request expiration handling
- Dashboard widget for pending approvals
- Export audit logs to CSV/PDF

## Notes

- Audit logging is non-blocking - if logging fails, the operation still proceeds
- Approval rules are evaluated in priority order (higher priority first)
- Multiple rules can apply to the same action
- Self-approval (default) means no approval request is created
- All audit logs are scoped to the organization

