<?php
require_once 'app/models/LeadAssignmentModel.php';
require_once 'app/models/LeadModel.php';
require_once 'app/models/StatusModel.php';
require_once 'app/models/UserModel.php';

class LeadAssignmentController extends Controller {
    private $leadAssignmentModel;
    private $leadModel;
    private $statusModel;
    private $userModel;

    public function __construct() {
        $this->leadAssignmentModel = new LeadAssignmentModel();
        $this->leadModel = new LeadModel();
        $this->statusModel = new StatusModel();
        $this->userModel = new UserModel();
    }

    /**
     * Display assigned leads page
     */
    public function assignedLeads() {
        require_role(['admin', 'sdr', 'manager']);
        
        $filters = [
            'assigned_by' => $_GET['assigned_by'] ?? '',
            'status_id' => $_GET['status_id'] ?? '',
            'assigned_date_from' => $_GET['assigned_date_from'] ?? '',
            'assigned_date_to' => $_GET['assigned_date_to'] ?? '',
            'assigned_to' => $_GET['assigned_to'] ?? '',
            'search' => $_GET['search'] ?? ''
        ];

        // Role-based filtering: SDRs can only see leads assigned to them
        $currentUser = auth_user();
        if ($currentUser['role'] === 'sdr') {
            $filters['assigned_to'] = $currentUser['id']; // Force filter to current SDR
        }

        $page = (int)($_GET['page'] ?? 1);
        $limit = 50;
        $offset = ($page - 1) * $limit;

        $leads = $this->leadAssignmentModel->getAssignedLeads($filters, $limit, $offset);
        $totalLeads = $this->leadAssignmentModel->getAssignedLeadsCount($filters);
        $totalPages = ceil($totalLeads / $limit);

        $statuses = $this->statusModel->all();
        $users = $this->leadAssignmentModel->getUsersForAssignment();
        $statistics = $this->leadAssignmentModel->getAssignmentStatistics($filters);

        include 'app/views/lead_assignment/assigned_leads.php';
    }

    /**
     * Assign a single lead
     */
    public function assignLead() {
        require_role(['admin', 'sdr', 'manager']);
        
        // Debug: Log all request data
        error_log("Assignment request - Method: " . $_SERVER['REQUEST_METHOD']);
        error_log("Assignment request - POST data: " . print_r($_POST, true));
        error_log("Assignment request - GET data: " . print_r($_GET, true));
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $leadId = (int)$_POST['lead_id'];
            $assignedTo = (int)$_POST['assigned_to'];
            $comment = trim($_POST['comment'] ?? '');
            $assignedBy = $_SESSION['id'];

            // Debug: Log all POST data
            error_log("Individual Assignment POST Data: " . print_r($_POST, true));
            error_log("Lead ID: $leadId, Assigned To: $assignedTo, Assigned By: $assignedBy");

            if ($leadId && $assignedTo) {
                try {
                    $success = $this->leadAssignmentModel->assignLead($leadId, $assignedTo, $assignedBy, $comment);
                    
                    if ($success) {
                        $_SESSION['success_message'] = 'Lead assigned successfully!';
                        error_log("Assignment successful for lead $leadId");
                    } else {
                        $_SESSION['error_message'] = 'Failed to assign lead. Please try again.';
                        error_log("Assignment failed for lead $leadId");
                    }
                } catch (Exception $e) {
                    $_SESSION['error_message'] = 'Error assigning lead: ' . $e->getMessage();
                    error_log("Assignment error for lead $leadId: " . $e->getMessage());
                }
            } else {
                $_SESSION['error_message'] = 'Please select a user to assign the lead to.';
                error_log("Assignment validation failed - Lead ID: $leadId, Assigned To: $assignedTo");
            }

            // Redirect back to the page that initiated the assignment
            $redirectUrl = $_POST['redirect_url'] ?? 'index.php?action=leads';
            
            // Clean up the redirect URL to avoid issues
            if (strpos($redirectUrl, 'http') === 0) {
                // If it's a full URL, extract just the path and query
                $parsedUrl = parse_url($redirectUrl);
                $redirectUrl = $parsedUrl['path'] ?? 'index.php?action=leads';
                if (!empty($parsedUrl['query'])) {
                    $redirectUrl .= '?' . $parsedUrl['query'];
                }
            }
            
            header("Location: $redirectUrl");
            exit;
        }
    }

    /**
     * Bulk assign multiple leads
     */
    public function bulkAssignLeads() {
        require_role(['admin', 'sdr', 'manager']);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $leadIds = $_POST['lead_ids'] ?? [];
            $assignedTo = (int)$_POST['assigned_to'];
            $comment = trim($_POST['comment'] ?? '');
            $assignedBy = $_SESSION['user']['id'];
            // pr(   $leadIds,1);
       
            if (!empty($leadIds) && $assignedTo) {
                try {
                    $results = $this->leadAssignmentModel->bulkAssignLeads($leadIds, $assignedTo, $assignedBy, $comment);
                    
                    $successCount = count(array_filter($results));
                    $totalCount = count($results);
                    
                    error_log("Bulk assignment attempt - Leads: " . implode(',', $leadIds) . ", Assigned To: $assignedTo, Success: $successCount/$totalCount");
                    
                    if ($successCount > 0) {
                        $_SESSION['success_message'] = "Successfully assigned $successCount out of $totalCount leads!";
                    } else {
                        $_SESSION['error_message'] = 'Failed to assign any leads. Please try again.';
                    }
                } catch (Exception $e) {
                    $_SESSION['error_message'] = 'Error assigning leads: ' . $e->getMessage();
                    error_log("Bulk assignment error: " . $e->getMessage());
                }
            } else {
                $_SESSION['error_message'] = 'Please select leads and a user to assign them to.';
                error_log("Bulk assignment validation failed - Lead IDs: " . implode(',', $leadIds) . ", Assigned To: $assignedTo");
            }

            // Redirect back to the page that initiated the assignment
            $redirectUrl = $_POST['redirect_url'] ?? 'index.php?action=leads';
            
            // Clean up the redirect URL to avoid issues
            if (strpos($redirectUrl, 'http') === 0) {
                // If it's a full URL, extract just the path and query
                $parsedUrl = parse_url($redirectUrl);
                $redirectUrl = $parsedUrl['path'] ?? 'index.php?action=leads';
                if (!empty($parsedUrl['query'])) {
                    $redirectUrl .= '?' . $parsedUrl['query'];
                }
            }
            
            header("Location: $redirectUrl");
            exit;
        }
    }

    /**
     * Unassign a lead
     */
    public function unassignLead() {
        require_role(['admin', 'sdr', 'manager']);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $leadId = (int)$_POST['lead_id'];
            $unassignedBy = $_SESSION['user_id'];

            if ($leadId) {
                $success = $this->leadAssignmentModel->unassignLead($leadId, $unassignedBy);
                
                if ($success) {
                    $_SESSION['success_message'] = 'Lead unassigned successfully!';
                } else {
                    $_SESSION['error_message'] = 'Failed to unassign lead. Please try again.';
                }
            } else {
                $_SESSION['error_message'] = 'Invalid lead ID.';
            }

            // Redirect back to the page that initiated the unassignment
            $redirectUrl = $_POST['redirect_url'] ?? 'index.php?action=leads';
            header("Location: $redirectUrl");
            exit;
        }
    }

    /**
     * Bulk unassign multiple leads
     */
    public function bulkUnassignLeads() {
        require_role(['admin', 'sdr', 'manager']);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $leadIds = $_POST['lead_ids'] ?? [];
            $unassignedBy = $_SESSION['user_id'];

            if (!empty($leadIds)) {
                $successCount = 0;
                foreach ($leadIds as $leadId) {
                    if ($this->leadAssignmentModel->unassignLead((int)$leadId, $unassignedBy)) {
                        $successCount++;
                    }
                }
                
                if ($successCount > 0) {
                    $_SESSION['success_message'] = "Successfully unassigned $successCount out of " . count($leadIds) . " leads!";
                } else {
                    $_SESSION['error_message'] = 'Failed to unassign any leads. Please try again.';
                }
            } else {
                $_SESSION['error_message'] = 'Please select leads to unassign.';
            }

            // Redirect back to the page that initiated the unassignment
            $redirectUrl = $_POST['redirect_url'] ?? 'index.php?action=leads';
            header("Location: $redirectUrl");
            exit;
        }
    }

    /**
     * Get assignment history for a lead (AJAX)
     */
    public function getAssignmentHistory() {
        require_role(['admin', 'sdr', 'manager']);
        
        $leadId = (int)($_GET['lead_id'] ?? 0);
        
        if ($leadId) {
            $history = $this->leadAssignmentModel->getLeadAssignmentHistory($leadId);
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'history' => $history]);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid lead ID']);
        }
        exit;
    }

    /**
     * Get current assignment for a lead (AJAX)
     */
    public function getCurrentAssignment() {
        require_role(['admin', 'sdr', 'manager']);
        
        $leadId = (int)($_GET['lead_id'] ?? 0);
        
        if ($leadId) {
            $assignment = $this->leadAssignmentModel->getCurrentAssignment($leadId);
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'assignment' => $assignment]);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid lead ID']);
        }
        exit;
    }

    /**
     * Get users for assignment dropdown (AJAX)
     */
    public function getUsersForAssignment() {
        require_role(['admin', 'sdr', 'manager']);
        
        $users = $this->leadAssignmentModel->getUsersForAssignment();
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'users' => $users]);
        exit;
    }

    /**
     * Get assignment statistics (AJAX)
     */
    public function getAssignmentStats() {
        require_role(['admin', 'sdr', 'manager']);
        
        $filters = [
            'assigned_by' => $_GET['assigned_by'] ?? '',
            'assigned_date_from' => $_GET['assigned_date_from'] ?? '',
            'assigned_date_to' => $_GET['assigned_date_to'] ?? ''
        ];

        $stats = $this->leadAssignmentModel->getAssignmentStatistics($filters);
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'stats' => $stats]);
        exit;
    }

    /**
     * Export assigned leads to CSV
     */
    public function exportAssignedLeads() {
        require_role(['admin', 'sdr', 'manager']);
        
        $filters = [
            'assigned_by' => $_GET['assigned_by'] ?? '',
            'status_id' => $_GET['status_id'] ?? '',
            'assigned_date_from' => $_GET['assigned_date_from'] ?? '',
            'assigned_date_to' => $_GET['assigned_date_to'] ?? '',
            'assigned_to' => $_GET['assigned_to'] ?? '',
            'search' => $_GET['search'] ?? ''
        ];

        // Get all assigned leads (no pagination for export)
        $leads = $this->leadAssignmentModel->getAssignedLeads($filters, 10000, 0);

        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="assigned_leads_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');
        
        // CSV headers
        fputcsv($output, [
            'Lead ID', 'Name', 'Company', 'Email', 'Phone', 'Status', 
            'Assigned To', 'Assigned By', 'Assigned Date', 'Comment',
            'Lead Source', 'Created Date'
        ]);

        // CSV data
        foreach ($leads as $lead) {
            fputcsv($output, [
                $lead['lead_id'],
                $lead['name'],
                $lead['company'],
                $lead['email'],
                $lead['phone'],
                $lead['status_name'],
                $lead['assigned_to_name'],
                $lead['assigned_by_name'],
                $lead['assigned_at'],
                $lead['assignment_comment'],
                $lead['lead_source_name'],
                $lead['created_at']
            ]);
        }

        fclose($output);
        exit;
    }
}
?>
