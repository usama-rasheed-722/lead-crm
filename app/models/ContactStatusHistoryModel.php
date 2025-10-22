<?php
class ContactStatusHistoryModel extends Model {

    // Log status change
    public function logStatusChange($leadId, $oldStatus, $newStatus, $changedBy) {
        $stmt = $this->pdo->prepare('
            INSERT INTO contact_status_history (lead_id, old_status, new_status, changed_by) 
            VALUES (?, ?, ?, ?)
        ');
        return $stmt->execute([$leadId, $oldStatus, $newStatus, $changedBy]);
    }

    // Get status history for a lead
    public function getByLeadId($leadId) {
        $stmt = $this->pdo->prepare('
            SELECT csh.*, u.username, u.full_name 
            FROM contact_status_history csh 
            LEFT JOIN users u ON csh.changed_by = u.id 
            WHERE csh.lead_id = ? 
            ORDER BY csh.changed_at DESC
        ');
        $stmt->execute([$leadId]);
        return $stmt->fetchAll();
    }

    // Get all status history with pagination
    public function getAll($limit = 100, $offset = 0) {
        $stmt = $this->pdo->prepare('
            SELECT csh.*, u.username, u.full_name, l.lead_id, l.company, l.name as contact_name
            FROM contact_status_history csh 
            LEFT JOIN users u ON csh.changed_by = u.id 
            LEFT JOIN leads l ON csh.lead_id = l.id
            ORDER BY csh.changed_at DESC 
            LIMIT ? OFFSET ?
        ');
        $stmt->bindValue(1, (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(2, (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Get status history count
    public function getCount() {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM contact_status_history');
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    // Get status changes by user
    public function getByUserId($userId, $limit = 100, $offset = 0) {
        $stmt = $this->pdo->prepare('
            SELECT csh.*, l.lead_id, l.company, l.name as contact_name
            FROM contact_status_history csh 
            LEFT JOIN leads l ON csh.lead_id = l.id
            WHERE csh.changed_by = ? 
            ORDER BY csh.changed_at DESC 
            LIMIT ? OFFSET ?
        ');
        $stmt->bindValue(1, (int)$userId, PDO::PARAM_INT);
        $stmt->bindValue(2, (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(3, (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Get status changes by date range
    public function getByDateRange($dateFrom, $dateTo, $limit = 100, $offset = 0) {
        $stmt = $this->pdo->prepare('
            SELECT csh.*, u.username, u.full_name, l.lead_id, l.company, l.name as contact_name
            FROM contact_status_history csh 
            LEFT JOIN users u ON csh.changed_by = u.id 
            LEFT JOIN leads l ON csh.lead_id = l.id
            WHERE DATE(csh.changed_at) >= ? AND DATE(csh.changed_at) <= ?
            ORDER BY csh.changed_at DESC 
            LIMIT ? OFFSET ?
        ');
        $stmt->bindValue(1, $dateFrom, PDO::PARAM_STR);
        $stmt->bindValue(2, $dateTo, PDO::PARAM_STR);
        $stmt->bindValue(3, (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(4, (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Get status change statistics
    public function getStatistics($dateFrom = null, $dateTo = null) {
        $whereClause = '';
        $params = [];
        
        if ($dateFrom && $dateTo) {
            $whereClause = 'WHERE DATE(changed_at) >= ? AND DATE(changed_at) <= ?';
            $params = [$dateFrom, $dateTo];
        }
        
        $stmt = $this->pdo->prepare("
            SELECT 
                new_status,
                COUNT(*) as count,
                COUNT(DISTINCT lead_id) as unique_leads
            FROM contact_status_history 
            {$whereClause}
            GROUP BY new_status 
            ORDER BY count DESC
        ");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
?>
