<?php
class QuotaModel extends Model {

    // Get all quotas for a specific user
    public function getQuotasByUserId($userId) {
        $stmt = $this->pdo->prepare('
            SELECT q.*, s.name as status_name, u.username as user_name 
            FROM quotas q 
            JOIN status s ON q.status_id = s.id 
            JOIN users u ON q.user_id = u.id 
            WHERE q.user_id = ? 
            ORDER BY s.sequence ASC, s.name ASC
        ');
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    // Get quota for a specific user and status
    public function getQuotaByUserAndStatus($userId, $statusId) {
        $stmt = $this->pdo->prepare('
            SELECT q.*, s.name as status_name 
            FROM quotas q 
            JOIN status s ON q.status_id = s.id 
            WHERE q.user_id = ? AND q.status_id = ?
        ');
        $stmt->execute([$userId, $statusId]);
        return $stmt->fetch();
    }

    // Create or update quota
    public function createOrUpdateQuota($userId, $statusId, $quotaLimit, $daysLimit) {
        try {
            $this->pdo->beginTransaction();
            
            // Check if quota already exists
            $existingQuota = $this->getQuotaByUserAndStatus($userId, $statusId);
            
            if ($existingQuota) {
                // Update existing quota
                $stmt = $this->pdo->prepare('
                    UPDATE quotas 
                    SET quota_limit = ?, days_limit = ?, updated_at = NOW() 
                    WHERE user_id = ? AND status_id = ?
                ');
                $result = $stmt->execute([$quotaLimit, $daysLimit, $userId, $statusId]);
            } else {
                // Create new quota
                $stmt = $this->pdo->prepare('
                    INSERT INTO quotas (user_id, status_id, quota_limit, days_limit) 
                    VALUES (?, ?, ?, ?)
                ');
                $result = $stmt->execute([$userId, $statusId, $quotaLimit, $daysLimit]);
            }
            
            $this->pdo->commit();
            return $result;
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    // Delete quota
    public function deleteQuota($quotaId) {
        $stmt = $this->pdo->prepare('DELETE FROM quotas WHERE id = ?');
        return $stmt->execute([$quotaId]);
    }

    // Get quota usage for a user and status
    public function getQuotaUsage($userId, $statusId, $daysLimit = 30) {
        $stmt = $this->pdo->prepare('
            SELECT COUNT(*) as usage_count 
            FROM contact_status_history 
            WHERE changed_by = ? 
            AND new_status_id = ? 
            AND changed_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
        ');
        $stmt->execute([$userId, $statusId, $daysLimit]);
        $result = $stmt->fetch();
        return (int)$result['usage_count'];
    }

    // Get quota details with usage for a user
    public function getQuotaDetailsWithUsage($userId) {
        $quotas = $this->getQuotasByUserId($userId);
        
        foreach ($quotas as &$quota) {
            $usage = $this->getQuotaUsage($userId, $quota['status_id'], $quota['days_limit']);
            $quota['usage_count'] = $usage;
            $quota['remaining'] = max(0, $quota['quota_limit'] - $usage);
            $quota['usage_percentage'] = $quota['quota_limit'] > 0 ? 
                round(($usage / $quota['quota_limit']) * 100, 2) : 0;
        }
        
        return $quotas;
    }

    // Get all quotas with usage (for admin view)
    public function getAllQuotasWithUsage() {
        $stmt = $this->pdo->prepare('
            SELECT q.*, s.name as status_name, u.username as user_name, u.full_name as user_full_name
            FROM quotas q 
            JOIN status s ON q.status_id = s.id 
            JOIN users u ON q.user_id = u.id 
            ORDER BY u.username ASC, s.sequence ASC
        ');
        $stmt->execute();
        $quotas = $stmt->fetchAll();
        
        foreach ($quotas as &$quota) {
            $usage = $this->getQuotaUsage($quota['user_id'], $quota['status_id'], $quota['days_limit']);
            $quota['usage_count'] = $usage;
            $quota['remaining'] = max(0, $quota['quota_limit'] - $usage);
            $quota['usage_percentage'] = $quota['quota_limit'] > 0 ? 
                round(($usage / $quota['quota_limit']) * 100, 2) : 0;
        }
        
        return $quotas;
    }

    // Check if user has exceeded quota for a status
    public function isQuotaExceeded($userId, $statusId) {
        $quota = $this->getQuotaByUserAndStatus($userId, $statusId);
        if (!$quota) {
            return false; // No quota set, so not exceeded
        }
        
        $usage = $this->getQuotaUsage($userId, $statusId, $quota['days_limit']);
        return $usage >= $quota['quota_limit'];
    }

    // Get quota status (available, warning, exceeded)
    public function getQuotaStatus($userId, $statusId) {
        $quota = $this->getQuotaByUserAndStatus($userId, $statusId);
        if (!$quota) {
            return 'unlimited';
        }
        
        $usage = $this->getQuotaUsage($userId, $statusId, $quota['days_limit']);
        $percentage = $quota['quota_limit'] > 0 ? ($usage / $quota['quota_limit']) * 100 : 0;
        
        if ($usage >= $quota['quota_limit']) {
            return 'exceeded';
        } elseif ($percentage >= 90) {
            return 'warning';
        } else {
            return 'available';
        }
    }

    // Get quota by ID
    public function getQuotaById($quotaId) {
        $stmt = $this->pdo->prepare('
            SELECT q.*, s.name as status_name, u.username as user_name 
            FROM quotas q 
            JOIN status s ON q.status_id = s.id 
            JOIN users u ON q.user_id = u.id 
            WHERE q.id = ?
        ');
        $stmt->execute([$quotaId]);
        return $stmt->fetch();
    }

    // Update quota
    public function updateQuota($quotaId, $quotaLimit, $daysLimit) {
        $stmt = $this->pdo->prepare('
            UPDATE quotas 
            SET quota_limit = ?, days_limit = ?, updated_at = NOW() 
            WHERE id = ?
        ');
        return $stmt->execute([$quotaLimit, $daysLimit, $quotaId]);
    }

    // Get quota summary for dashboard
    public function getQuotaSummary() {
        $stmt = $this->pdo->prepare('
            SELECT 
                COUNT(DISTINCT user_id) as users_with_quotas,
                COUNT(*) as total_quotas,
                SUM(quota_limit) as total_quota_limit
            FROM quotas
        ');
        $stmt->execute();
        return $stmt->fetch();
    }
}
?>
