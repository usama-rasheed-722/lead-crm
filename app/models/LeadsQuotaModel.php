<?php
// app/models/LeadsQuotaModel.php
class LeadsQuotaModel extends Model {
    
    // Create a new leads quota assignment
    public function create($userId, $statusId, $quotaCount, $assignedDate = null, $explanation = null) {
        if (!$assignedDate) {
            $assignedDate = date('Y-m-d');
        }
        
        $stmt = $this->pdo->prepare("
            INSERT INTO leads_quota (user_id, status_id, quota_count, assigned_date, explanation)
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
            quota_count = VALUES(quota_count),
            explanation = VALUES(explanation),
            updated_at = CURRENT_TIMESTAMP
        ");
        
        $result = $stmt->execute([$userId, $statusId, $quotaCount, $assignedDate, $explanation]);
        
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
            $this->assignLeadsToQuota($quotaId, $statusId, $quotaCount,$userId);
            
            return $quotaId;
        }
        
        return false;
    }
    
    // Assign leads to a quota automatically
    private function assignLeadsToQuota($quotaId, $statusId, $quotaCount,$userId) {
        // Get available leads for this status that aren't already assigned to quotas today
        $stmt = $this->pdo->prepare("SELECT sdr_id from users WHERE id = ? ");
        $stmt->execute([$userId]);
        $sdrId = $stmt->fetch(PDO::FETCH_COLUMN); 
      
        $stmt = $this->pdo->prepare("
                WITH lqcte AS (
                SELECT 
                    lq.status_id AS q_status_id,
                    lqa.lead_id AS q_lead_id,
                    lqa.assigned_at
                FROM leads_quota lq
                LEFT JOIN lead_quota_assignments lqa 
                    ON lq.id = lqa.leads_quota_id
            )
            SELECT 
                l.*
            FROM leads l
            LEFT JOIN lqcte 
                ON l.id = lqcte.q_lead_id
            WHERE 
                l.sdr_id = ? AND l.status_id = ? 
                AND (
                    lqcte.q_lead_id IS NULL              
                    OR l.status_id != lqcte.q_status_id
                )
            ORDER BY l.created_at ASC
            LIMIT ?;
        ");
        
        $stmt->execute([$sdrId,$statusId,  $quotaCount]);
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
            SELECT l.*, lqa.assigned_at, lqa.completed_at, lqa.id as assignment_id, s.name as status_name
            FROM leads l
            INNER JOIN lead_quota_assignments lqa ON l.id = lqa.lead_id
            INNER JOIN leads_quota lq ON lqa.leads_quota_id = lq.id
            LEFT JOIN status s ON l.status_id = s.id
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
    
    // Mark quota as completed when lead status changes (called from LeadModel)
    public function markQuotaCompletedOnStatusChange($leadId, $newStatusId) {
        // Find the assignment for this lead that's not completed
        $stmt = $this->pdo->prepare("
            SELECT lqa.id as assignment_id, lq.status_id as quota_status_id
            FROM lead_quota_assignments lqa
            INNER JOIN leads_quota lq ON lqa.leads_quota_id = lq.id
            WHERE lqa.lead_id = ? AND lqa.completed_at IS NULL
        ");
        $stmt->execute([$leadId]);
        $assignments = $stmt->fetchAll();
        
        $completedCount = 0;
        foreach ($assignments as $assignment) {
            // Only mark as completed if the new status matches the quota status
            // or if the lead is being moved to a "completed" status
            if ($assignment['quota_status_id'] == $newStatusId) {
                $this->markLeadCompleted($assignment['assignment_id']);
                $completedCount++;
            }
        }
        
        return $completedCount;
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
    
    // Process daily rollover for incomplete quotas
    public function processDailyRollover($date = null) {
        if (!$date) {
            $date = date('Y-m-d');
        }
        
        $previousDate = date('Y-m-d', strtotime($date . ' -1 day'));
        
        // Get all incomplete quotas from previous day
        $stmt = $this->pdo->prepare("
            SELECT lq.*, 
                   COUNT(lqa.id) as assigned_leads,
                   COUNT(CASE WHEN lqa.completed_at IS NOT NULL THEN 1 END) as completed_leads
            FROM leads_quota lq
            LEFT JOIN lead_quota_assignments lqa ON lq.id = lqa.leads_quota_id
            WHERE lq.assigned_date = ?
            GROUP BY lq.id
            HAVING completed_leads < lq.quota_count
        ");
        $stmt->execute([$previousDate]);
        $incompleteQuotas = $stmt->fetchAll();
        
        $rolloverCount = 0;
        
        foreach ($incompleteQuotas as $quota) {
            $remainingLeads = $quota['quota_count'] - $quota['completed_leads'];
            
            if ($remainingLeads > 0) {
                // Check if quota already exists for today
                $existingQuota = $this->getQuotaByUserStatusDate($quota['user_id'], $quota['status_id'], $date);
                
                if ($existingQuota) {
                    // Update existing quota with rollover
                    $newQuotaCount = $existingQuota['quota_count'] + $remainingLeads;
                    $this->updateQuotaCount($existingQuota['id'], $newQuotaCount);
                    
                    // Get incomplete leads from previous quota
                    $incompleteLeads = $this->getIncompleteLeadsFromQuota($quota['id']);
                    
                    // Assign these leads to today's quota
                    if (!empty($incompleteLeads)) {
                        $this->assignLeadsToExistingQuota($existingQuota['id'], $incompleteLeads);
                    }
                } else {
                    // Create new quota for today with rollover (preserve explanation)
                    $newQuotaId = $this->create($quota['user_id'], $quota['status_id'], $remainingLeads, $date, $quota['explanation']);
                    
                    if ($newQuotaId) {
                        // Get incomplete leads from previous quota
                        $incompleteLeads = $this->getIncompleteLeadsFromQuota($quota['id']);
                        
                        // Assign these leads to new quota
                        if (!empty($incompleteLeads)) {
                            $this->assignLeadsToExistingQuota($newQuotaId, $incompleteLeads);
                        }
                    }
                }
                
                // Log the rollover
                $this->logQuotaRollover($quota['user_id'], $quota['status_id'], $previousDate, $date, $remainingLeads);
                
                $rolloverCount++;
            }
        }
        
        return $rolloverCount;
    }
    
    // Get quota by user, status, and date
    private function getQuotaByUserStatusDate($userId, $statusId, $date) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM leads_quota 
            WHERE user_id = ? AND status_id = ? AND assigned_date = ?
        ");
        $stmt->execute([$userId, $statusId, $date]);
        return $stmt->fetch();
    }
    
    // Get incomplete leads from a quota
    private function getIncompleteLeadsFromQuota($quotaId) {
        $stmt = $this->pdo->prepare("
            SELECT lead_id FROM lead_quota_assignments 
            WHERE leads_quota_id = ? AND completed_at IS NULL
        ");
        $stmt->execute([$quotaId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    // Assign leads to existing quota
    private function assignLeadsToExistingQuota($quotaId, $leadIds) {
        if (empty($leadIds)) {
            return 0;
        }
        
        $placeholders = str_repeat('?,', count($leadIds) - 1) . '?';
        $stmt = $this->pdo->prepare("
            INSERT IGNORE INTO lead_quota_assignments (leads_quota_id, lead_id)
            VALUES " . str_repeat("(?, ?),", count($leadIds) - 1) . "(?, ?)
        ");
        
        $params = [];
        foreach ($leadIds as $leadId) {
            $params[] = $quotaId;
            $params[] = $leadId;
        }
        
        $stmt->execute($params);
        return $stmt->rowCount();
    }
    
    // Log quota rollover
    private function logQuotaRollover($userId, $statusId, $fromDate, $toDate, $rolloverCount) {
        $stmt = $this->pdo->prepare("
            INSERT INTO quota_logs (user_id, status_id, quota_assigned, quota_used, quota_carry_forward, log_date, created_at)
            VALUES (?, ?, 0, 0, ?, ?, CURRENT_TIMESTAMP)
            ON DUPLICATE KEY UPDATE
            quota_carry_forward = quota_carry_forward + VALUES(quota_carry_forward),
            updated_at = CURRENT_TIMESTAMP
        ");
        $stmt->execute([$userId, $statusId, $rolloverCount, $toDate]);
    }
    
    // Get quota history with rollover information
    public function getQuotaHistory($userId, $startDate = null, $endDate = null) {
        if (!$startDate) {
            $startDate = date('Y-m-d', strtotime('-30 days'));
        }
        if (!$endDate) {
            $endDate = date('Y-m-d');
        }
        
        $stmt = $this->pdo->prepare("
            SELECT 
                lq.*,
                s.name as status_name,
                COUNT(lqa.id) as assigned_leads,
                COUNT(CASE WHEN lqa.completed_at IS NOT NULL THEN 1 END) as completed_leads,
                (lq.quota_count - COUNT(CASE WHEN lqa.completed_at IS NOT NULL THEN 1 END)) as remaining_leads,
                ql.quota_carry_forward
            FROM leads_quota lq
            LEFT JOIN status s ON lq.status_id = s.id
            LEFT JOIN lead_quota_assignments lqa ON lq.id = lqa.leads_quota_id
            LEFT JOIN quota_logs ql ON lq.user_id = ql.user_id AND lq.status_id = ql.status_id AND lq.assigned_date = ql.log_date
            WHERE lq.user_id = ? AND lq.assigned_date BETWEEN ? AND ?
            GROUP BY lq.id
            ORDER BY lq.assigned_date DESC, s.name
        ");
        $stmt->execute([$userId, $startDate, $endDate]);
        return $stmt->fetchAll();
    }
    
    // Get comprehensive quota report for admin
    public function getQuotaReport($startDate = null, $endDate = null, $userId = null) {
        if (!$startDate) {
            $startDate = date('Y-m-d', strtotime('-30 days'));
        }
        if (!$endDate) {
            $endDate = date('Y-m-d');
        }
        
        $whereClause = "lq.assigned_date BETWEEN ? AND ?";
        $params = [$startDate, $endDate];
        
        if ($userId) {
            $whereClause .= " AND lq.user_id = ?";
            $params[] = $userId;
        }
        
        $stmt = $this->pdo->prepare("
            SELECT 
                lq.*,
                u.full_name as user_name,
                s.name as status_name,
                COUNT(lqa.id) as assigned_leads,
                COUNT(CASE WHEN lqa.completed_at IS NOT NULL THEN 1 END) as completed_leads,
                (lq.quota_count - COUNT(CASE WHEN lqa.completed_at IS NOT NULL THEN 1 END)) as remaining_leads,
                ql.quota_carry_forward,
                CASE 
                    WHEN lq.quota_count > 0 THEN 
                        ROUND((COUNT(CASE WHEN lqa.completed_at IS NOT NULL THEN 1 END) / lq.quota_count) * 100, 2)
                    ELSE 0 
                END as completion_percentage
            FROM leads_quota lq
            LEFT JOIN users u ON lq.user_id = u.id
            LEFT JOIN status s ON lq.status_id = s.id
            LEFT JOIN lead_quota_assignments lqa ON lq.id = lqa.leads_quota_id
            LEFT JOIN quota_logs ql ON lq.user_id = ql.user_id AND lq.status_id = ql.status_id AND lq.assigned_date = ql.log_date
            WHERE $whereClause
            GROUP BY lq.id
            ORDER BY lq.assigned_date DESC, u.full_name, s.name
        ");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    // Get quota statistics summary
    public function getQuotaStatistics($startDate = null, $endDate = null, $userId = null) {
        if (!$startDate) {
            $startDate = date('Y-m-d', strtotime('-30 days'));
        }
        if (!$endDate) {
            $endDate = date('Y-m-d');
        }
        
        $whereClause = "lq.assigned_date BETWEEN ? AND ?";
        $params = [$startDate, $endDate];
        
        if ($userId) {
            $whereClause .= " AND lq.user_id = ?";
            $params[] = $userId;
        }
        
        $stmt = $this->pdo->prepare("
            SELECT 
                COUNT(DISTINCT lq.id) as total_quotas,
                COUNT(DISTINCT lq.user_id) as total_users,
                SUM(lq.quota_count) as total_assigned,
                SUM(CASE WHEN lqa.completed_at IS NOT NULL THEN 1 ELSE 0 END) as total_completed,
                SUM(lq.quota_count) - SUM(CASE WHEN lqa.completed_at IS NOT NULL THEN 1 ELSE 0 END) as total_remaining
            FROM leads_quota lq
            LEFT JOIN lead_quota_assignments lqa ON lq.id = lqa.leads_quota_id
            WHERE $whereClause
        ");
        $stmt->execute($params);
        $result = $stmt->fetch();
        
        // Calculate average completion rate
        $avgCompletionRate = 0;
        if ($result['total_assigned'] > 0) {
            $avgCompletionRate = ($result['total_completed'] / $result['total_assigned']) * 100;
        }
        
        // Return statistics
        $stats = [
            'total_quotas' => (int)$result['total_quotas'],
            'total_users' => (int)$result['total_users'],
            'total_assigned' => (int)$result['total_assigned'],
            'total_completed' => (int)$result['total_completed'],
            'total_remaining' => (int)$result['total_remaining'],
            'avg_completion_rate' => round($avgCompletionRate, 2)
        ];
        
        return $stats;
    }
}
?>
