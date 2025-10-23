<?php
class QuotaController extends Controller {
    protected $quotaModel;
    protected $quotaLogModel;
    protected $statusModel;
    protected $userModel;
    
    public function __construct() {
        parent::__construct();
        $this->quotaModel = new QuotaModel();
        $this->quotaLogModel = new QuotaLogModel();
        $this->statusModel = new StatusModel();
        $this->userModel = new UserModel();
    }
    
    // List all quotas (admin only)
    public function index() {
        require_role(['admin']); // Only admins can view all quotas
        
        $quotas = $this->quotaModel->getAllQuotasWithUsage();
        
        $this->view('quota/index', [
            'quotas' => $quotas
        ]);
    }
    
    // Show quota management for a specific user (admin only)
    public function manageUserQuotas($userId) {
        require_role(['admin']);
        
        if (!$userId) {
            $this->redirect('index.php?action=users&error=' . urlencode('Invalid user ID'));
        }
        
        $user = $this->userModel->findById($userId);
        if (!$user) {
            $this->redirect('index.php?action=users&error=' . urlencode('User not found'));
        }
        
        $quotas = $this->quotaModel->getQuotaDetailsWithUsage($userId);
        $statuses = $this->statusModel->all();
        
        $this->view('quota/manage_user', [
            'user' => $user,
            'quotas' => $quotas,
            'statuses' => $statuses
        ]);
    }
    
    // Create or update quota (admin only)
    public function store() {
        require_role(['admin']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('index.php?action=users');
        }
        
        $userId = (int)($_POST['user_id'] ?? 0);
        $statusId = (int)($_POST['status_id'] ?? 0);
        $quotaLimit = (int)($_POST['quota_limit'] ?? 0);
        $daysLimit = (int)($_POST['days_limit'] ?? 30);
        
        if (!$userId || !$statusId || $quotaLimit < 0) {
            $this->redirect("index.php?action=manage_user_quotas&id={$userId}&error=" . urlencode('Invalid quota data'));
        }
        
        try {
            $this->quotaModel->createOrUpdateQuota($userId, $statusId, $quotaLimit, $daysLimit);
            $this->redirect("index.php?action=manage_user_quotas&id={$userId}&success=" . urlencode('Quota updated successfully'));
        } catch (Exception $e) {
            $this->redirect("index.php?action=manage_user_quotas&id={$userId}&error=" . urlencode('Failed to update quota: ' . $e->getMessage()));
        }
    }
    
    // Update quota (admin only)
    public function update($quotaId) {
        require_role(['admin']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$quotaId) {
            $this->redirect('index.php?action=quota_management');
        }
        
        $quotaLimit = (int)($_POST['quota_limit'] ?? 0);
        $daysLimit = (int)($_POST['days_limit'] ?? 30);
        
        if ($quotaLimit < 0) {
            $this->redirect('index.php?action=quota_management&error=' . urlencode('Invalid quota limit'));
        }
        
        try {
            $this->quotaModel->updateQuota($quotaId, $quotaLimit, $daysLimit);
            $this->redirect('index.php?action=quota_management&success=' . urlencode('Quota updated successfully'));
        } catch (Exception $e) {
            $this->redirect('index.php?action=quota_management&error=' . urlencode('Failed to update quota: ' . $e->getMessage()));
        }
    }
    
    // Delete quota (admin only)
    public function delete($quotaId) {
        require_role(['admin']);
        
        if (!$quotaId) {
            $this->redirect('index.php?action=quota_management');
        }
        
        try {
            $this->quotaModel->deleteQuota($quotaId);
            $this->redirect('index.php?action=quota_management&success=' . urlencode('Quota deleted successfully'));
        } catch (Exception $e) {
            $this->redirect('index.php?action=quota_management&error=' . urlencode('Failed to delete quota: ' . $e->getMessage()));
        }
    }
    
    // Get quota usage for AJAX requests
    public function getQuotaUsage() {
        $userId = (int)($_GET['user_id'] ?? 0);
        $statusId = (int)($_GET['status_id'] ?? 0);
        
        if (!$userId || !$statusId) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid parameters']);
            exit;
        }
        
        $quota = $this->quotaModel->getQuotaByUserAndStatus($userId, $statusId);
        if (!$quota) {
            http_response_code(404);
            echo json_encode(['error' => 'Quota not found']);
            exit;
        }
        
        $usage = $this->quotaModel->getQuotaUsage($userId, $statusId, $quota['days_limit']);
        $remaining = max(0, $quota['quota_limit'] - $usage);
        $percentage = $quota['quota_limit'] > 0 ? round(($usage / $quota['quota_limit']) * 100, 2) : 0;
        
        header('Content-Type: application/json');
        echo json_encode([
            'quota_limit' => $quota['quota_limit'],
            'usage_count' => $usage,
            'remaining' => $remaining,
            'percentage' => $percentage,
            'days_limit' => $quota['days_limit']
        ]);
        exit;
    }
    
    // Get user's quota status for AJAX requests
    public function getQuotaStatus() {
        $userId = (int)($_GET['user_id'] ?? 0);
        $statusId = (int)($_GET['status_id'] ?? 0);
        
        if (!$userId || !$statusId) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid parameters']);
            exit;
        }
        
        $status = $this->quotaModel->getQuotaStatus($userId, $statusId);
        $quota = $this->quotaModel->getQuotaByUserAndStatus($userId, $statusId);
        
        header('Content-Type: application/json');
        echo json_encode([
            'status' => $status,
            'quota' => $quota
        ]);
        exit;
    }
    
    // User view: Get user's own quota information
    public function getUserQuotas() {
        $user = auth_user();
        if (!$user) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        
        $quotas = $this->quotaModel->getQuotaDetailsWithUsage($user['id']);
        
        header('Content-Type: application/json');
        echo json_encode(['quotas' => $quotas]);
        exit;
    }
    
    // Get quota summary for dashboard
    public function getQuotaSummary() {
        $user = auth_user();
        if (!$user) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        
        $logDate = $_GET['date'] ?? date('Y-m-d');
        $summary = $this->quotaLogModel->getQuotaSummary($user['id'], $logDate);
        
        header('Content-Type: application/json');
        echo json_encode(['summary' => $summary]);
        exit;
    }
}
?>
