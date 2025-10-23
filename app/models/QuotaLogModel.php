<?php
// app/models/QuotaLogModel.php
class QuotaLogModel extends Model {
    
    // Create or update daily quota log
    public function createOrUpdate($userId, $statusId, $quotaAssigned, $quotaUsed, $quotaCarryForward, $logDate) {
        $stmt = $this->pdo->prepare("
            INSERT INTO quota_logs (user_id, status_id, quota_assigned, quota_used, quota_carry_forward, log_date)
            VALUES (?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
            quota_assigned = VALUES(quota_assigned),
            quota_used = VALUES(quota_used),
            quota_carry_forward = VALUES(quota_carry_forward),
            updated_at = CURRENT_TIMESTAMP
        ");
        
        return $stmt->execute([$userId, $statusId, $quotaAssigned, $quotaUsed, $quotaCarryForward, $logDate]);
    }
    
    // Get quota log for specific user, status, and date
    public function getByUserStatusDate($userId, $statusId, $logDate) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM quota_logs 
            WHERE user_id = ? AND status_id = ? AND log_date = ?
        ");
        $stmt->execute([$userId, $statusId, $logDate]);
        return $stmt->fetch();
    }
    
    // Get all quota logs for a user on a specific date
    public function getByUserDate($userId, $logDate) {
        $stmt = $this->pdo->prepare("
            SELECT ql.*, s.name as status_name 
            FROM quota_logs ql
            LEFT JOIN status s ON ql.status_id = s.id
            WHERE ql.user_id = ? AND ql.log_date = ?
            ORDER BY s.name
        ");
        $stmt->execute([$userId, $logDate]);
        return $stmt->fetchAll();
    }
    
    // Get unfulfilled quota from previous days
    public function getUnfulfilledQuota($userId, $statusId, $beforeDate) {
        $stmt = $this->pdo->prepare("
            SELECT SUM(quota_assigned - quota_used) as unfulfilled
            FROM quota_logs 
            WHERE user_id = ? AND status_id = ? AND log_date < ? AND quota_assigned > quota_used
        ");
        $stmt->execute([$userId, $statusId, $beforeDate]);
        $result = $stmt->fetch();
        return $result['unfulfilled'] ?? 0;
    }
    
    // Get total available quota (today's + carry forward)
    public function getTotalAvailableQuota($userId, $statusId, $logDate) {
        // Get today's quota
        $todayQuota = $this->getByUserStatusDate($userId, $statusId, $logDate);
        $todayAssigned = $todayQuota['quota_assigned'] ?? 0;
        
        // Get unfulfilled quota from previous days
        $unfulfilledQuota = $this->getUnfulfilledQuota($userId, $statusId, $logDate);
        
        return $todayAssigned + $unfulfilledQuota;
    }
    
    // Update quota usage
    public function updateUsage($userId, $statusId, $logDate, $additionalUsage = 1) {
        $stmt = $this->pdo->prepare("
            UPDATE quota_logs 
            SET quota_used = quota_used + ?, updated_at = CURRENT_TIMESTAMP
            WHERE user_id = ? AND status_id = ? AND log_date = ?
        ");
        return $stmt->execute([$additionalUsage, $userId, $statusId, $logDate]);
    }
    
    // Get quota usage for a date range
    public function getUsageByDateRange($userId, $statusId, $startDate, $endDate) {
        $stmt = $this->pdo->prepare("
            SELECT log_date, quota_assigned, quota_used, quota_carry_forward
            FROM quota_logs 
            WHERE user_id = ? AND status_id = ? AND log_date BETWEEN ? AND ?
            ORDER BY log_date
        ");
        $stmt->execute([$userId, $statusId, $startDate, $endDate]);
        return $stmt->fetchAll();
    }
    
    // Initialize daily quota for a user based on their assigned quotas
    public function initializeDailyQuota($userId, $logDate) {
        $quotaModel = new QuotaModel();
        $userQuotas = $quotaModel->getQuotasByUserId($userId);
        
        foreach ($userQuotas as $quota) {
            // Check if quota log already exists for this date
            $existing = $this->getByUserStatusDate($userId, $quota['status_id'], $logDate);
            
            if (!$existing) {
                // Get unfulfilled quota from previous days
                $carryForward = $this->getUnfulfilledQuota($userId, $quota['status_id'], $logDate);
                
                // Create new quota log
                $this->createOrUpdate(
                    $userId, 
                    $quota['status_id'], 
                    $quota['quota_limit'], 
                    0, 
                    $carryForward, 
                    $logDate
                );
            }
        }
    }
    
    // Get quota summary for dashboard
    public function getQuotaSummary($userId, $logDate = null) {
        if (!$logDate) {
            $logDate = date('Y-m-d');
        }
        
        // Initialize daily quota if not exists
        $this->initializeDailyQuota($userId, $logDate);
        
        $stmt = $this->pdo->prepare("
            SELECT 
                ql.*,
                s.name as status_name,
                (ql.quota_assigned + ql.quota_carry_forward) as total_available,
                (ql.quota_assigned + ql.quota_carry_forward - ql.quota_used) as remaining
            FROM quota_logs ql
            LEFT JOIN status s ON ql.status_id = s.id
            WHERE ql.user_id = ? AND ql.log_date = ?
            ORDER BY s.name
        ");
        $stmt->execute([$userId, $logDate]);
        return $stmt->fetchAll();
    }
}
?>
