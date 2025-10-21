<?php

class LeadSourceModel {
    private $pdo;
    
    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }
    
    // Get all lead sources
    public function all() {
        $stmt = $this->pdo->query("SELECT * FROM lead_sources ORDER BY name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get active lead sources only
    public function getActive() {
        $stmt = $this->pdo->prepare("SELECT * FROM lead_sources WHERE is_active = 1 ORDER BY name ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get lead source by ID
    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM lead_sources WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Get lead source by name
    public function getByName($name) {
        $stmt = $this->pdo->prepare("SELECT * FROM lead_sources WHERE name = ?");
        $stmt->execute([$name]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Create new lead source
    public function create($name, $description = null, $isActive = true) {
        $stmt = $this->pdo->prepare("
            INSERT INTO lead_sources (name, description, is_active) 
            VALUES (?, ?, ?)
        ");
        return $stmt->execute([$name, $description, $isActive ? 1 : 0]);
    }
    
    // Update lead source
    public function update($id, $name, $description = null, $isActive = true) {
        $stmt = $this->pdo->prepare("
            UPDATE lead_sources 
            SET name = ?, description = ?, is_active = ?, updated_at = CURRENT_TIMESTAMP 
            WHERE id = ?
        ");
        return $stmt->execute([$name, $description, $isActive ? 1 : 0, $id]);
    }
    
    // Delete lead source
    public function delete($id) {
        // Check if lead source is being used by any leads
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM leads WHERE lead_source_id = ?");
        $stmt->execute([$id]);
        $count = $stmt->fetchColumn();
        
        if ($count > 0) {
            throw new Exception("Cannot delete lead source. It is being used by {$count} lead(s).");
        }
        
        $stmt = $this->pdo->prepare("DELETE FROM lead_sources WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    // Check if lead source name exists (excluding current ID for updates)
    public function nameExists($name, $excludeId = null) {
        $sql = "SELECT COUNT(*) FROM lead_sources WHERE name = ?";
        $params = [$name];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }
    
    // Get lead source usage count
    public function getUsageCount($id) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM leads WHERE lead_source_id = ?");
        $stmt->execute([$id]);
        return $stmt->fetchColumn();
    }
    
    // Toggle active status
    public function toggleActive($id) {
        $stmt = $this->pdo->prepare("
            UPDATE lead_sources 
            SET is_active = NOT is_active, updated_at = CURRENT_TIMESTAMP 
            WHERE id = ?
        ");
        return $stmt->execute([$id]);
    }
    
    // Get lead sources with usage count
    public function getAllWithUsage() {
        $stmt = $this->pdo->query("
            SELECT 
                ls.*,
                COUNT(l.id) as usage_count
            FROM lead_sources ls
            LEFT JOIN leads l ON ls.id = l.lead_source_id
            GROUP BY ls.id
            ORDER BY ls.name ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
