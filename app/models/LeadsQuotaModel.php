<?php
// app/models/LeadsQuotaModel.php
class LeadsQuotaModel extends Model {
    
    // Create a new leads quota assignment
    public function create($userId, $statusId, $quotaCount, $assignedDate = null) {
        if (!$assignedDate) {
            $assignedDate = date('Y-m-d');
        }
        
        $stmt = $this->pdo->prepare("
            INSERT INTO leads_quota (user_id, status_id, quota_count, assigned_date)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
            quota_count = VALUES(quota_count),
            updated_at = CURRENT_TIMESTAMP
        ");
        
        $result = $stmt->execute([$userId, $statusId, $quotaCount, $assignedDate]);
        
        if ($result) {
            $quotaId = $this->pdo->lastInsertId();
            if (!$quotaId) {
                // If it was an update, get the existing ID
                $stmt = $this->pdo->prepare("
                    SELECT id FROM leads_quota 
                    WHERE user_id = ? AND status_id = ? AND assigned_date = ?
                ");
                $stmt->execute([$userId, $statusId, $assignedDate]);
                $quotaId = $stmt->fetchColumn();
            }
            
            // Automatically assign leads to this quota
            $this->assignLeadsToQuota($quotaId, $statusId, $quotaCount);
            
            return $quotaId;
        }
        
        return false;
    }
    
    // Assign leads to a quota automatically
    private function assignLeadsToQuota($quotaId, $statusId, $quotaCount) {
        // Get available leads for this status that aren't already assigned to quotas today
        $stmt = $this->pdo->prepare("
            SELECT l.id 
            FROM leads l
            LEFT JOIN lead_quota_assignments lqa ON l.id = lqa.lead_id
            LEFT JOIN leads_quota lq ON lqa.leads_quota_id = lq.id
            WHERE l.status_id = ? 
            AND (lq.assigned_date IS NULL OR lq.assigned_date != CURDATE())
            ORDER BY l.created_at ASC
            LIMIT ?
        ");
        $stmt->execute([$statusId, $quotaCount]);
        $leads = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Assign these leads to the quota
        if (!empty($leads)) {
            $placeholders = str_repeat('?,', count($leads) - 1) . '?';
            $stmt = $this->pdo->prepare("
                INSERT IGNORE INTO lead_quota_assignments (leads_quota_id, lead_id)
                VALUES " . str_repeat("(?, ?),", count($leads) - 1) . "(?, ?)
            ");
            
            $params = [];
            foreach ($leads as $leadId) {
                $params[] = $quotaId;
                $params[] = $leadId;
            }
            
            $stmt->execute($params);
        }
        
        return count($leads);
    }
    
    // Get quota by ID
    public function getById($id) {
        $stmt = $this->pdo->prepare("
            SELECT lq.*, u.full_name as user_name, s.name as status_name
            FROM leads_quota lq
            LEFT JOIN users u ON lq.user_id = u.id
            LEFT JOIN status s ON lq.status_id = s.id
            WHERE lq.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    // Get quotas for a user on a specific date
    public function getByUserDate($userId, $date = null) {
        if (!$date) {
            $date = date('Y-m-d');
        }
        
        $stmt = $this->pdo->prepare("
            SELECT lq.*, s.name as status_name,
                   COUNT(lqa.id) as assigned_leads,
                   COUNT(CASE WHEN lqa.completed_at IS NOT NULL THEN 1 END) as completed_leads
            FROM leads_quota lq
            LEFT JOIN status s ON lq.status_id = s.id
            LEFT JOIN lead_quota_assignments lqa ON lq.id = lqa.leads_quota_id
            WHERE lq.user_id = ? AND lq.assigned_date = ?
            GROUP BY lq.id
            ORDER BY s.name
        ");
        $stmt->execute([$userId, $date]);
        return $stmt->fetchAll();
    }
    
    // Get quota summary for a user (today's quotas)
    public function getQuotaSummary($userId, $date = null) {
        if (!$date) {
            $date = date('Y-m-d');
        }
        
        $stmt = $this->pdo->prepare("
            SELECT 
                lq.*,
                s.name as status_name,
                COUNT(lqa.id) as assigned_leads,
                COUNT(CASE WHEN lqa.completed_at IS NOT NULL THEN 1 END) as completed_leads,
                (lq.quota_count - COUNT(CASE WHEN lqa.completed_at IS NOT NULL THEN 1 END)) as remaining_leads
            FROM leads_quota lq
            LEFT JOIN status s ON lq.status_id = s.id
            LEFT JOIN lead_quota_assignments lqa ON lq.id = lqa.leads_quota_id
            WHERE lq.user_id = ? AND lq.assigned_date = ?
            GROUP BY lq.id
            ORDER BY s.name
        ");
        $stmt->execute([$userId, $date]);
        return $stmt->fetchAll();
    }
    
    // Get assigned leads for a quota
    public function getAssignedLeads($quotaId, $limit = 100, $offset = 0) {
        $stmt = $this->pdo->prepare("
            SELECT l.*, lqa.assigned_at, lqa.completed_at, lqa.id as assignment_id
            FROM leads l
            INNER JOIN lead_quota_assignments lqa ON l.id = lqa.lead_id
            WHERE lqa.leads_quota_id = ?
            ORDER BY lqa.assigned_at ASC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$quotaId, $limit, $offset]);
        return $stmt->fetchAll();
    }
    
    // Get assigned leads for a user's quota on a specific date
    public function getAssignedLeadsByUserDate($userId, $statusId, $date = null, $limit = 100, $offset = 0) {
        if (!$date) {
            $date = date('Y-m-d');
        }
        
        $stmt = $this->pdo->prepare("
            SELECT l.*, lqa.assigned_at, lqa.completed_at, lqa.id as assignment_id
            FROM leads l
            INNER JOIN lead_quota_assignments lqa ON l.id = lqa.lead_id
            INNER JOIN leads_quota lq ON lqa.leads_quota_id = lq.id
            WHERE lq.user_id = ? AND lq.status_id = ? AND lq.assigned_date = ?
            ORDER BY lqa.assigned_at ASC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$userId, $statusId, $date, $limit, $offset]);
        return $stmt->fetchAll();
    }
    
    // Mark a lead as completed in quota
    public function markLeadCompleted($assignmentId) {
        $stmt = $this->pdo->prepare("
            UPDATE lead_quota_assignments 
            SET completed_at = CURRENT_TIMESTAMP 
            WHERE id = ? AND completed_at IS NULL
        ");
        return $stmt->execute([$assignmentId]);
    }
    
    // Mark a lead as not completed (undo completion)
    public function markLeadNotCompleted($assignmentId) {
        $stmt = $this->pdo->prepare("
            UPDATE lead_quota_assignments 
            SET completed_at = NULL 
            WHERE id = ?
        ");
        return $stmt->execute([$assignmentId]);
    }
    
    // Get all quotas (admin view)
    public function getAllQuotas($date = null) {
        if (!$date) {
            $date = date('Y-m-d');
        }
        
        $stmt = $this->pdo->prepare("
            SELECT lq.*, u.full_name as user_name, s.name as status_name,
                   COUNT(lqa.id) as assigned_leads,
                   COUNT(CASE WHEN lqa.completed_at IS NOT NULL THEN 1 END) as completed_leads
            FROM leads_quota lq
            LEFT JOIN users u ON lq.user_id = u.id
            LEFT JOIN status s ON lq.status_id = s.id
            LEFT JOIN lead_quota_assignments lqa ON lq.id = lqa.leads_quota_id
            WHERE lq.assigned_date = ?
            GROUP BY lq.id
            ORDER BY u.full_name, s.name
        ");
        $stmt->execute([$date]);
        return $stmt->fetchAll();
    }
    
    // Delete a quota
    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM leads_quota WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    // Update quota count
    public function updateQuotaCount($id, $quotaCount) {
        $stmt = $this->pdo->prepare("
            UPDATE leads_quota 
            SET quota_count = ?, updated_at = CURRENT_TIMESTAMP 
            WHERE id = ?
        ");
        return $stmt->execute([$quotaCount, $id]);
    }
}
?>
