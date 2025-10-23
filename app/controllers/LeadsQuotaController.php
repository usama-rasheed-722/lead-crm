<?php
// app/controllers/LeadsQuotaController.php
class LeadsQuotaController extends Controller {
    protected $leadsQuotaModel;
    protected $userModel;
    protected $statusModel;
    protected $leadModel;
    
    public function __construct() {
        parent::__construct();
        $this->leadsQuotaModel = new LeadsQuotaModel();
        $this->userModel = new UserModel();
        $this->statusModel = new StatusModel();
        $this->leadModel = new LeadModel();
    }
    
    // Admin: Assign leads quota
    public function assign() {
        require_role(['admin']);
        
        $users = $this->userModel->all();
        $statuses = $this->statusModel->all();
        
        $this->view('leads_quota/assign', [
            'users' => $users,
            'statuses' => $statuses
        ]);
    }
    
    // Admin: Store quota assignment
    public function store() {
        require_role(['admin']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('index.php?action=leads_quota_assign');
        }
        
        $userId = (int)($_POST['user_id'] ?? 0);
        $statusId = (int)($_POST['status_id'] ?? 0);
        $quotaCount = (int)($_POST['quota_count'] ?? 0);
        $assignedDate = $_POST['assigned_date'] ?? date('Y-m-d');
        
        if (empty($userId) || empty($statusId) || empty($quotaCount)) {
            $this->redirect('index.php?action=leads_quota_assign&error=' . urlencode('All fields are required'));
        }
        
        try {
            $quotaId = $this->leadsQuotaModel->create($userId, $statusId, $quotaCount, $assignedDate);
            
            if ($quotaId) {
                $this->redirect('index.php?action=leads_quota_manage&success=' . urlencode('Quota assigned successfully'));
            } else {
                $this->redirect('index.php?action=leads_quota_assign&error=' . urlencode('Failed to assign quota'));
            }
        } catch (Exception $e) {
            $this->redirect('index.php?action=leads_quota_assign&error=' . urlencode('Error: ' . $e->getMessage()));
        }
    }
    
    // Admin: Manage quotas
    public function manage() {
        require_role(['admin']);
        
        $date = $_GET['date'] ?? date('Y-m-d');
        $quotas = $this->leadsQuotaModel->getAllQuotas($date);
        
        $this->view('leads_quota/manage', [
            'quotas' => $quotas,
            'date' => $date
        ]);
    }
    
    // SDR: View assigned leads quota
    public function sdrView() {
        require_role(['sdr']);
        
        $user = auth_user();
        $statusId = $_GET['status_id'] ?? null;
        $date = $_GET['date'] ?? date('Y-m-d');
        $page = (int)($_GET['page'] ?? 1);
        $limit = 50;
        $offset = ($page - 1) * $limit;
        
        // Get quota summary for the user
        $quotaSummary = $this->leadsQuotaModel->getQuotaSummary($user['id'], $date);
        
        // Get assigned leads if a specific status is selected
        $assignedLeads = [];
        $totalLeads = 0;
        $selectedStatus = null;
        
        if ($statusId) {
            $assignedLeads = $this->leadsQuotaModel->getAssignedLeadsByUserDate($user['id'], $statusId, $date, $limit, $offset);
            
            // Get total count for pagination
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) as total
                FROM leads l
                INNER JOIN lead_quota_assignments lqa ON l.id = lqa.lead_id
                INNER JOIN leads_quota lq ON lqa.leads_quota_id = lq.id
                WHERE lq.user_id = ? AND lq.status_id = ? AND lq.assigned_date = ?
            ");
            $stmt->execute([$user['id'], $statusId, $date]);
            $totalLeads = $stmt->fetchColumn();
            
            // Get status name
            $selectedStatus = $this->statusModel->getById($statusId);
        }
        
        $this->view('leads_quota/sdr_view', [
            'quotaSummary' => $quotaSummary,
            'assignedLeads' => $assignedLeads,
            'selectedStatus' => $selectedStatus,
            'date' => $date,
            'page' => $page,
            'limit' => $limit,
            'totalLeads' => $totalLeads,
            'totalPages' => ceil($totalLeads / $limit)
        ]);
    }
    
    // AJAX: Mark lead as completed
    public function markCompleted() {
        require_role(['sdr']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            exit;
        }
        
        $assignmentId = (int)($_POST['assignment_id'] ?? 0);
        
        if (empty($assignmentId)) {
            http_response_code(400);
            echo json_encode(['error' => 'Assignment ID required']);
            exit;
        }
        
        try {
            $result = $this->leadsQuotaModel->markLeadCompleted($assignmentId);
            
            header('Content-Type: application/json');
            echo json_encode(['success' => $result]);
            exit;
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
            exit;
        }
    }
    
    // AJAX: Mark lead as not completed
    public function markNotCompleted() {
        require_role(['sdr']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            exit;
        }
        
        $assignmentId = (int)($_POST['assignment_id'] ?? 0);
        
        if (empty($assignmentId)) {
            http_response_code(400);
            echo json_encode(['error' => 'Assignment ID required']);
            exit;
        }
        
        try {
            $result = $this->leadsQuotaModel->markLeadNotCompleted($assignmentId);
            
            header('Content-Type: application/json');
            echo json_encode(['success' => $result]);
            exit;
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
            exit;
        }
    }
    
    // AJAX: Get quota summary
    public function getQuotaSummary() {
        require_role(['sdr']);
        
        $user = auth_user();
        $date = $_GET['date'] ?? date('Y-m-d');
        
        $summary = $this->leadsQuotaModel->getQuotaSummary($user['id'], $date);
        
        header('Content-Type: application/json');
        echo json_encode(['summary' => $summary]);
        exit;
    }
    
    // Admin: Delete quota
    public function delete() {
        require_role(['admin']);
        
        $id = (int)($_GET['id'] ?? 0);
        
        if (empty($id)) {
            $this->redirect('index.php?action=leads_quota_manage&error=' . urlencode('Invalid quota ID'));
        }
        
        try {
            $result = $this->leadsQuotaModel->delete($id);
            
            if ($result) {
                $this->redirect('index.php?action=leads_quota_manage&success=' . urlencode('Quota deleted successfully'));
            } else {
                $this->redirect('index.php?action=leads_quota_manage&error=' . urlencode('Failed to delete quota'));
            }
        } catch (Exception $e) {
            $this->redirect('index.php?action=leads_quota_manage&error=' . urlencode('Error: ' . $e->getMessage()));
        }
    }
}
?>
