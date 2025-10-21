<?php
class StatusModel extends BaseModel {

    // Get all statuses
    public function all() {
        $stmt = $this->pdo->prepare('SELECT * FROM status ORDER BY name ASC');
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Get status by ID
    public function getById($id) {
        $stmt = $this->pdo->prepare('SELECT * FROM status WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // Get status by name
    public function getByName($name) {
        $stmt = $this->pdo->prepare('SELECT * FROM status WHERE name = ?');
        $stmt->execute([$name]);
        return $stmt->fetch();
    }

    // Create new status
    public function create($name, $restrictBulkUpdate = false, $isDefault = false) {
        // If this is being set as default, unset any existing default
        if ($isDefault) {
            $this->unsetDefaultStatus();
        }
        
        $stmt = $this->pdo->prepare('INSERT INTO status (name, restrict_bulk_update, is_default) VALUES (?, ?, ?)');
        $stmt->execute([$name, $restrictBulkUpdate ? 1 : 0, $isDefault ? 1 : 0]);
        return $this->pdo->lastInsertId();
    }

    // Update status
    public function update($id, $name, $restrictBulkUpdate = false, $isDefault = false) {
        // If this is being set as default, unset any existing default
        if ($isDefault) {
            $this->unsetDefaultStatus();
        }
        
        $stmt = $this->pdo->prepare('UPDATE status SET name = ?, restrict_bulk_update = ?, is_default = ? WHERE id = ?');
        return $stmt->execute([$name, $restrictBulkUpdate ? 1 : 0, $isDefault ? 1 : 0, $id]);
    }

    // Delete status
    public function delete($id) {
        // Check if status is being used by any leads
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM leads WHERE status = (SELECT name FROM status WHERE id = ?)');
        $stmt->execute([$id]);
        $count = $stmt->fetchColumn();
        
        if ($count > 0) {
            throw new Exception('Cannot delete status that is currently being used by leads');
        }
        
        $stmt = $this->pdo->prepare('DELETE FROM status WHERE id = ?');
        return $stmt->execute([$id]);
    }

    // Check if status name exists (excluding current ID for updates)
    public function nameExists($name, $excludeId = null) {
        $sql = 'SELECT COUNT(*) FROM status WHERE name = ?';
        $params = [$name];
        
        if ($excludeId) {
            $sql .= ' AND id != ?';
            $params[] = $excludeId;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }

    // Get default status
    public function getDefaultStatus() {
        $stmt = $this->pdo->prepare('SELECT * FROM status WHERE is_default = 1 LIMIT 1');
        $stmt->execute();
        return $stmt->fetch();
    }
    
    // Get default status name
    public function getDefaultStatusName() {
        $stmt = $this->pdo->prepare('SELECT name FROM status WHERE is_default = 1 LIMIT 1');
        $stmt->execute();
        $result = $stmt->fetch();
        return $result ? $result['name'] : 'New Lead';
    }
    
    // Unset all default statuses (ensure only one default exists)
    private function unsetDefaultStatus() {
        $stmt = $this->pdo->prepare('UPDATE status SET is_default = 0 WHERE is_default = 1');
        return $stmt->execute();
    }
    
    // Set a status as default
    public function setAsDefault($id) {
        // First unset any existing default
        $this->unsetDefaultStatus();
        
        // Set the new default
        $stmt = $this->pdo->prepare('UPDATE status SET is_default = 1 WHERE id = ?');
        return $stmt->execute([$id]);
    }

    // Get custom fields for a status
    public function getCustomFields($statusId) {
        $stmt = $this->pdo->prepare('SELECT * FROM status_custom_fields WHERE status_id = ? ORDER BY field_order ASC, id ASC');
        $stmt->execute([$statusId]);
        return $stmt->fetchAll();
    }

    // Get custom fields by status name
    public function getCustomFieldsByName($statusName) {
        $stmt = $this->pdo->prepare('
            SELECT scf.* FROM status_custom_fields scf 
            JOIN status s ON scf.status_id = s.id 
            WHERE s.name = ? 
            ORDER BY scf.field_order ASC, scf.id ASC
        ');
        $stmt->execute([$statusName]);
        return $stmt->fetchAll();
    }

    // Create custom field for a status
    public function createCustomField($statusId, $fieldName, $fieldLabel, $fieldType, $fieldOptions = null, $isRequired = false, $fieldOrder = 0) {
        $stmt = $this->pdo->prepare('
            INSERT INTO status_custom_fields (status_id, field_name, field_label, field_type, field_options, is_required, field_order) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ');
        return $stmt->execute([$statusId, $fieldName, $fieldLabel, $fieldType, $fieldOptions, $isRequired ? 1 : 0, $fieldOrder]);
    }

    // Update custom field
    public function updateCustomField($fieldId, $fieldName, $fieldLabel, $fieldType, $fieldOptions = null, $isRequired = false, $fieldOrder = 0) {
        $stmt = $this->pdo->prepare('
            UPDATE status_custom_fields 
            SET field_name = ?, field_label = ?, field_type = ?, field_options = ?, is_required = ?, field_order = ? 
            WHERE id = ?
        ');
        return $stmt->execute([$fieldName, $fieldLabel, $fieldType, $fieldOptions, $isRequired ? 1 : 0, $fieldOrder, $fieldId]);
    }

    // Delete custom field
    public function deleteCustomField($fieldId) {
        $stmt = $this->pdo->prepare('DELETE FROM status_custom_fields WHERE id = ?');
        return $stmt->execute([$fieldId]);
    }

    // Get custom field by ID
    public function getCustomFieldById($fieldId) {
        $stmt = $this->pdo->prepare('SELECT * FROM status_custom_fields WHERE id = ?');
        $stmt->execute([$fieldId]);
        return $stmt->fetch();
    }

    // Check if status restricts bulk updates
    public function restrictsBulkUpdate($statusId) {
        $stmt = $this->pdo->prepare('SELECT restrict_bulk_update FROM status WHERE id = ?');
        $stmt->execute([$statusId]);
        $result = $stmt->fetch();
        return $result ? (bool)$result['restrict_bulk_update'] : false;
    }

    // Check if status restricts bulk updates by name
    public function restrictsBulkUpdateByName($statusName) {
        $stmt = $this->pdo->prepare('SELECT restrict_bulk_update FROM status WHERE name = ?');
        $stmt->execute([$statusName]);
        $result = $stmt->fetch();
        return $result ? (bool)$result['restrict_bulk_update'] : false;
    }
}
?>
