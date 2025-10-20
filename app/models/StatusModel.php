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
    public function create($name) {
        $stmt = $this->pdo->prepare('INSERT INTO status (name) VALUES (?)');
        $stmt->execute([$name]);
        return $this->pdo->lastInsertId();
    }

    // Update status
    public function update($id, $name) {
        $stmt = $this->pdo->prepare('UPDATE status SET name = ? WHERE id = ?');
        return $stmt->execute([$name, $id]);
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

    // Get default status (New Lead)
    public function getDefaultStatus() {
        $stmt = $this->pdo->prepare('SELECT * FROM status WHERE name = "New Lead" LIMIT 1');
        $stmt->execute();
        return $stmt->fetch();
    }
}
?>
