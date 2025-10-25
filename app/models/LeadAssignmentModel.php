<?php
class LeadAssignmentModel extends Model {

    /**
     * Assign a lead to a user
     * @param int $leadId Lead ID
     * @param int $assignedTo User ID to assign to
     * @param int $assignedBy User ID who is assigning
     * @param string $comment Assignment comment
     * @return bool Success status
     */
    public function assignLead($leadId, $assignedTo, $assignedBy, $comment = '') {
        try {
            $this->pdo->beginTransaction();

            // Deactivate previous assignments for this lead
            $stmt = $this->pdo->prepare("UPDATE lead_assignments SET is_active = 0 WHERE lead_id = ?");
            $stmt->execute([$leadId]);

            // Create new assignment record
            $stmt = $this->pdo->prepare("
                INSERT INTO lead_assignments (lead_id, assigned_to, assigned_by, comment, is_active)
                VALUES (?, ?, ?, ?, 1)
            ");
            $stmt->execute([$leadId, $assignedTo, $assignedBy, $comment]);

            // Update the lead record with current assignment
            $stmt = $this->pdo->prepare("
                UPDATE leads 
                SET assigned_to = ?, assigned_by = ?, assigned_at = NOW(), assignment_comment = ?
                WHERE id = ?
            ");
            $stmt->execute([$assignedTo, $assignedBy, $comment, $leadId]);

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Lead assignment error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Bulk assign multiple leads to a user
     * @param array $leadIds Array of lead IDs
     * @param int $assignedTo User ID to assign to
     * @param int $assignedBy User ID who is assigning
     * @param string $comment Assignment comment
     * @return array Results with success/failure for each lead
     */
    public function bulkAssignLeads($leadIds, $assignedTo, $assignedBy, $comment = '') {
        $results = [];
        
        foreach ($leadIds as $leadId) {
            $results[$leadId] = $this->assignLead($leadId, $assignedTo, $assignedBy, $comment);
        }
        
        return $results;
    }

    /**
     * Get assignment history for a lead
     * @param int $leadId Lead ID
     * @return array Assignment history
     */
    public function getLeadAssignmentHistory($leadId) {
        $stmt = $this->pdo->prepare("
            SELECT 
                la.*,
                u1.username as assigned_to_name,
                u1.full_name as assigned_to_full_name,
                u2.username as assigned_by_name,
                u2.full_name as assigned_by_full_name
            FROM lead_assignments la
            LEFT JOIN users u1 ON la.assigned_to = u1.id
            LEFT JOIN users u2 ON la.assigned_by = u2.id
            WHERE la.lead_id = ?
            ORDER BY la.assigned_at DESC
        ");
        $stmt->execute([$leadId]);
        return $stmt->fetchAll();
    }

    /**
     * Get current assignment for a lead
     * @param int $leadId Lead ID
     * @return array Current assignment or null
     */
    public function getCurrentAssignment($leadId) {
        $stmt = $this->pdo->prepare("
            SELECT 
                l.assigned_to,
                l.assigned_by,
                l.assigned_at,
                l.assignment_comment,
                u1.username as assigned_to_name,
                u1.full_name as assigned_to_full_name,
                u2.username as assigned_by_name,
                u2.full_name as assigned_by_full_name
            FROM leads l
            LEFT JOIN users u1 ON l.assigned_to = u1.id
            LEFT JOIN users u2 ON l.assigned_by = u2.id
            WHERE l.id = ? AND l.assigned_to IS NOT NULL
        ");
        $stmt->execute([$leadId]);
        return $stmt->fetch();
    }

    /**
     * Get assigned leads with filters
     * @param array $filters Filter options
     * @param int $limit Limit for pagination
     * @param int $offset Offset for pagination
     * @return array Assigned leads
     */
    public function getAssignedLeads($filters = [], $limit = 50, $offset = 0) {
        $whereConditions = ["l.assigned_to IS NOT NULL"];
        $params = [];

        // SDR filter (assigned by)
        if (!empty($filters['assigned_by'])) {
            $whereConditions[] = "l.assigned_by = ?";
            $params[] = $filters['assigned_by'];
        }

        // Status filter
        if (!empty($filters['status_id'])) {
            $whereConditions[] = "l.status_id = ?";
            $params[] = $filters['status_id'];
        }

        // Assigned date filter
        if (!empty($filters['assigned_date_from'])) {
            $whereConditions[] = "DATE(l.assigned_at) >= ?";
            $params[] = $filters['assigned_date_from'];
        }

        if (!empty($filters['assigned_date_to'])) {
            $whereConditions[] = "DATE(l.assigned_at) <= ?";
            $params[] = $filters['assigned_date_to'];
        }

        // Assigned to filter
        if (!empty($filters['assigned_to'])) {
            $whereConditions[] = "l.assigned_to = ?";
            $params[] = $filters['assigned_to'];
        }

        // Search filter
        if (!empty($filters['search'])) {
            $whereConditions[] = "(l.name LIKE ? OR l.company LIKE ? OR l.email LIKE ? OR l.lead_id LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $whereClause = implode(' AND ', $whereConditions);

        $stmt = $this->pdo->prepare("
            SELECT 
                l.*,
                s.name as status_name,
                u1.username as assigned_to_name,
                u1.full_name as assigned_to_full_name,
                u2.username as assigned_by_name,
                u2.full_name as assigned_by_full_name,
                ls.name as lead_source_name
            FROM leads l
            LEFT JOIN status s ON l.status_id = s.id
            LEFT JOIN users u1 ON l.assigned_to = u1.id
            LEFT JOIN users u2 ON l.assigned_by = u2.id
            LEFT JOIN lead_sources ls ON l.lead_source_id = ls.id
            WHERE $whereClause
            ORDER BY l.assigned_at DESC
            LIMIT ? OFFSET ?
        ");

        $params[] = $limit;
        $params[] = $offset;
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get count of assigned leads with filters
     * @param array $filters Filter options
     * @return int Count of assigned leads
     */
    public function getAssignedLeadsCount($filters = []) {
        $whereConditions = ["l.assigned_to IS NOT NULL"];
        $params = [];

        // Apply same filters as getAssignedLeads
        if (!empty($filters['assigned_by'])) {
            $whereConditions[] = "l.assigned_by = ?";
            $params[] = $filters['assigned_by'];
        }

        if (!empty($filters['status_id'])) {
            $whereConditions[] = "l.status_id = ?";
            $params[] = $filters['status_id'];
        }

        if (!empty($filters['assigned_date_from'])) {
            $whereConditions[] = "DATE(l.assigned_at) >= ?";
            $params[] = $filters['assigned_date_from'];
        }

        if (!empty($filters['assigned_date_to'])) {
            $whereConditions[] = "DATE(l.assigned_at) <= ?";
            $params[] = $filters['assigned_date_to'];
        }

        if (!empty($filters['assigned_to'])) {
            $whereConditions[] = "l.assigned_to = ?";
            $params[] = $filters['assigned_to'];
        }

        if (!empty($filters['search'])) {
            $whereConditions[] = "(l.name LIKE ? OR l.company LIKE ? OR l.email LIKE ? OR l.lead_id LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $whereClause = implode(' AND ', $whereConditions);

        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as count
            FROM leads l
            WHERE $whereClause
        ");
        $stmt->execute($params);
        $result = $stmt->fetch();
        return (int)$result['count'];
    }

    /**
     * Get all users for assignment dropdown
     * @return array Users list
     */
    public function getUsersForAssignment() {
        $stmt = $this->pdo->prepare("
            SELECT id, username, full_name, role
            FROM users 
            WHERE role IN ('admin', 'sdr', 'manager')
            ORDER BY full_name, username
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Unassign a lead (remove assignment)
     * @param int $leadId Lead ID
     * @param int $unassignedBy User ID who is unassigning
     * @return bool Success status
     */
    public function unassignLead($leadId, $unassignedBy) {
        try {
            $this->pdo->beginTransaction();

            // Deactivate current assignment
            $stmt = $this->pdo->prepare("UPDATE lead_assignments SET is_active = 0 WHERE lead_id = ? AND is_active = 1");
            $stmt->execute([$leadId]);

            // Clear assignment from leads table
            $stmt = $this->pdo->prepare("
                UPDATE leads 
                SET assigned_to = NULL, assigned_by = NULL, assigned_at = NULL, assignment_comment = NULL
                WHERE id = ?
            ");
            $stmt->execute([$leadId]);

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Lead unassignment error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get assignment statistics
     * @param array $filters Filter options
     * @return array Statistics
     */
    public function getAssignmentStatistics($filters = []) {
        $whereConditions = ["l.assigned_to IS NOT NULL"];
        $params = [];

        // Apply filters
        if (!empty($filters['assigned_by'])) {
            $whereConditions[] = "l.assigned_by = ?";
            $params[] = $filters['assigned_by'];
        }

        if (!empty($filters['assigned_date_from'])) {
            $whereConditions[] = "DATE(l.assigned_at) >= ?";
            $params[] = $filters['assigned_date_from'];
        }

        if (!empty($filters['assigned_date_to'])) {
            $whereConditions[] = "DATE(l.assigned_at) <= ?";
            $params[] = $filters['assigned_date_to'];
        }

        $whereClause = implode(' AND ', $whereConditions);

        $stmt = $this->pdo->prepare("
            SELECT 
                COUNT(*) as total_assigned,
                COUNT(DISTINCT l.assigned_to) as unique_assignees,
                COUNT(DISTINCT l.assigned_by) as unique_assigners
            FROM leads l
            WHERE $whereClause
        ");
        $stmt->execute($params);
        return $stmt->fetch();
    }
}
?>
