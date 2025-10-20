<?php
// app/controllers/LeadController.php
class LeadController extends Controller {
    protected $leadModel;
    protected $userModel;
    
    public function __construct() {
        parent::__construct();
        $this->leadModel = new LeadModel();
        $this->userModel = new UserModel();
    }
    
    // List all leads with search and filters
    public function index() {
        $user = auth_user();
        $search = $_GET['search'] ?? '';
        $filters = [
            'sdr_id' => $_GET['sdr_id'] ?? '',
            'duplicate_status' => $_GET['duplicate_status'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? '',
            'lead_source' => $_GET['lead_source'] ?? '',
            'status' => $_GET['status'] ?? ''
        ];
        
        // Remove empty filters
        $filters = array_filter($filters);
        
        $page = (int)($_GET['page'] ?? 1);
        $limit = 100;
        $offset = ($page - 1) * $limit;
        
        // Role-based filtering
        if ($user['role'] === 'sdr') {
            $filters['sdr_id'] = $user['sdr_id'] ?? $user['id'];
        }
        
        $leads = $this->leadModel->search($search, $filters, $limit, $offset);
        $total = $this->leadModel->countSearch($search, $filters);
        $totalPages = max(1, (int)ceil($total / $limit));
        $users = $this->userModel->all();
        
        // Load statuses for filters and bulk update
        $statusModel = new StatusModel();
        $statuses = $statusModel->all();
        
        $this->view('leads/index', [
            'leads' => $leads,
            'users' => $users,
            'statuses' => $statuses,
            'search' => $search,
            'filters' => $filters,
            'page' => $page,
            'total' => $total,
            'totalPages' => $totalPages,
            'limit' => $limit
        ]);
    }
    
    // View single lead
    public function viewLead($id) {
        if (!$id) {
            $this->redirect('index.php?action=leads');
        }
        
        $lead = $this->leadModel->getById($id);
        if (!$lead) {
            $this->redirect('index.php?action=leads');
        }
        
        // Check permissions
        $user = auth_user();
        if ($user['role'] === 'sdr' && $lead['sdr_id'] != ($user['sdr_id'] ?? $user['id'])) {
            http_response_code(403);
            echo 'Access denied';
            exit;
        }
        
        // Get lead notes
        $noteModel = new NoteModel();
        $notes = $noteModel->getByLeadId($id);

        // Get status history
        $historyModel = new ContactStatusHistoryModel();
        $statusHistory = $historyModel->getByLeadId($id);
        
        $this->view('leads/view', [
            'lead' => $lead,
            'notes' => $notes,
            'statusHistory' => $statusHistory
        ]);
    }
    
    // Show create form
    public function create() {
        $users = $this->userModel->all();
        $this->view('leads/form', [
            'lead' => null,
            'users' => $users,
            'action' => 'create'
        ]);
    }
    
    // Store new lead
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('index.php?action=lead_add');
        }
        $user = auth_user();
        
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'lead_id' => trim($_POST['lead_id'] ?? generateNextSDR(empty($_POST['sdr_id'])?($user['sdr_id'] ?? $user['id']):$_POST['sdr_id'])),
            'company' => trim($_POST['company'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'linkedin' => trim($_POST['linkedin'] ?? ''),
            'website' => trim($_POST['website'] ?? ''),
            'clutch' => trim($_POST['clutch'] ?? ''),
            'job_title' => trim($_POST['job_title'] ?? ''),
            'industry' => trim($_POST['industry'] ?? ''),
            'lead_source' => trim($_POST['lead_source'] ?? ''),
            'tier' => trim($_POST['tier'] ?? ''),
            'lead_status' => trim($_POST['lead_status'] ?? ''),
            'insta' => trim($_POST['insta'] ?? ''),
            'social_profile' => trim($_POST['social_profile'] ?? ''),
            'address' => trim($_POST['address'] ?? ''),
            'description_information' => trim($_POST['description_information'] ?? ''),
            'whatsapp' => trim($_POST['whatsapp'] ?? ''),
            'next_step' => trim($_POST['next_step'] ?? ''),
            'other' => trim($_POST['other'] ?? ''),
            'status' => trim($_POST['status'] ?? ''),
            'country' => trim($_POST['country'] ?? ''),
            'sdr_id' => $user['role'] === 'admin'
                ? (empty($_POST['sdr_id']) ? (int)($user['sdr_id'] ?? $user['id']) : (int)$_POST['sdr_id'])
                : (int)($user['sdr_id'] ?? $user['id']),
            'notes' => trim($_POST['notes'] ?? ''),
            'created_by' => $user['id']
        ];
        
        try {
            $leadId = $this->leadModel->create($data);
            $this->redirect("index.php?action=lead_view&id={$leadId}");
        } catch (Exception $e) {
            $error = 'Failed to create lead: ' . $e->getMessage();
            $users = $this->userModel->all();
            $this->view('leads/form', [
                'lead' => $data,
                'users' => $users,
                'action' => 'create',
                'error' => $error
            ]);
        }
    }
    
    // Show edit form
    public function edit($id) {
        if (!$id) {
            $this->redirect('index.php?action=leads');
        }
        
        $lead = $this->leadModel->getById($id);
        if (!$lead) {
            $this->redirect('index.php?action=leads');
        }
        
        // Check permissions
        $user = auth_user();
        if ($user['role'] === 'sdr' && $lead['sdr_id'] != ($user['sdr_id'] ?? $user['id'])) {
            http_response_code(403);
            echo 'Access denied';
            exit;
        }
        
        $users = $this->userModel->all();
        $this->view('leads/form', [
            'lead' => $lead,
            'users' => $users,
            'action' => 'edit'
        ]);
    }
    
    // Update lead
    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$id) {
            $this->redirect('index.php?action=leads');
        }
        
        $lead = $this->leadModel->getById($id);
        if (!$lead) {
            $this->redirect('index.php?action=leads');
        }
        
        // Check permissions
        $user = auth_user();
        if ($user['role'] === 'sdr' && $lead['sdr_id'] != ($user['sdr_id'] ?? $user['id'])) {
            http_response_code(403);
            echo 'Access denied';
            exit;
        }
        
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'company' => trim($_POST['company'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'linkedin' => trim($_POST['linkedin'] ?? ''),
            'website' => trim($_POST['website'] ?? ''),
            'clutch' => trim($_POST['clutch'] ?? ''),
            'job_title' => trim($_POST['job_title'] ?? ''),
            'industry' => trim($_POST['industry'] ?? ''),
            'lead_source' => trim($_POST['lead_source'] ?? ''),
            'tier' => trim($_POST['tier'] ?? ''),
            'lead_status' => trim($_POST['lead_status'] ?? ''),
            'insta' => trim($_POST['insta'] ?? ''),
            'social_profile' => trim($_POST['social_profile'] ?? ''),
            'address' => trim($_POST['address'] ?? ''),
            'description_information' => trim($_POST['description_information'] ?? ''),
            'whatsapp' => trim($_POST['whatsapp'] ?? ''),
            'next_step' => trim($_POST['next_step'] ?? ''),
            'other' => trim($_POST['other'] ?? ''),
            'status' => trim($_POST['status'] ?? ''),
            'country' => trim($_POST['country'] ?? ''),
            'sdr_id' => $user['role'] === 'admin' ? (int)($_POST['sdr_id'] ?? $lead['sdr_id']) : $lead['sdr_id'],
            'notes' => trim($_POST['notes'] ?? '')
        ];
        
        try {
            $this->leadModel->update($id, $data);
            $this->redirect("index.php?action=lead_view&id={$id}");
        } catch (Exception $e) {
            $error = 'Failed to update lead: ' . $e->getMessage();
            $users = $this->userModel->all();
            $this->view('leads/form', [
                'lead' => array_merge($lead, $data),
                'users' => $users,
                'action' => 'edit',
                'error' => $error
            ]);
        }
    }
    
    // Delete lead
    public function delete($id) {
        if (!$id) {
            $this->redirect('index.php?action=leads');
        }
        
        $lead = $this->leadModel->getById($id);
        if (!$lead) {
            $this->redirect('index.php?action=leads');
        }
        
        // Check permissions
        $user = auth_user();
        if ($user['role'] === 'sdr' && $lead['sdr_id'] != $user['id']) {
            http_response_code(403);
            echo 'Access denied';
            exit;
        }
        
        try {
            $this->leadModel->delete($id);
            $this->redirect('index.php?action=leads');
        } catch (Exception $e) {
            $this->redirect('index.php?action=leads&error=' . urlencode('Failed to delete lead'));
        }
    }
    
    // Generate SDR number for a lead
    public function generateSDR($id) {
        if (!$id) {
            $this->redirect('index.php?action=leads');
        }
        
        $lead = $this->leadModel->getById($id);
        if (!$lead) {
            $this->redirect('index.php?action=leads');
        }
        
        // Check permissions
        $user = auth_user();
        if ($user['role'] === 'sdr' && $lead['sdr_id'] != $user['id']) {
            http_response_code(403);
            echo 'Access denied';
            exit;
        }
        
        try {
            $sdrNumber = generateSDRNumber($id, $lead['sdr_id']);
            $this->redirect("index.php?action=lead_view&id={$id}&success=" . urlencode("SDR number generated: {$sdrNumber}"));
        } catch (Exception $e) {
            $this->redirect("index.php?action=lead_view&id={$id}&error=" . urlencode('Failed to generate SDR number'));
        }
    }
    
 
    // Bulk delete leads
    public function bulkDelete() {
        require_role(['admin']); // Only admins can do bulk operations
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('index.php?action=leads');
        }
        
        $leadIds = $_POST['lead_ids'] ?? '';
        if (empty($leadIds)) {
            $this->redirect('index.php?action=leads&error=' . urlencode('No leads selected for deletion'));
        }
        
        $ids = array_filter(array_map('intval', explode(',', $leadIds)));
        if (empty($ids)) {
            $this->redirect('index.php?action=leads&error=' . urlencode('Invalid lead IDs provided'));
        }
        
        try {
            $pdo = $this->pdo;
            $pdo->beginTransaction();
            
            $placeholders = str_repeat('?,', count($ids) - 1) . '?';
            $stmt = $pdo->prepare("DELETE FROM leads WHERE id IN ($placeholders)");
            $stmt->execute($ids);
            
            $deletedCount = $stmt->rowCount();
            $pdo->commit();
            
            $this->redirect("index.php?action=leads&success=" . urlencode("Successfully deleted {$deletedCount} lead(s)"));
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $this->redirect("index.php?action=leads&error=" . urlencode('Failed to delete leads: ' . $e->getMessage()));
        }
    }
    
    // Find duplicates for a lead
    public function findDuplicates($id) {
        if (!$id) {
            $this->redirect('index.php?action=leads');
        }
        
        $lead = $this->leadModel->getById($id);
        if (!$lead) {
            $this->redirect('index.php?action=leads');
        }
        
        // Check permissions
        $user = auth_user();
        if ($user['role'] === 'sdr' && $lead['sdr_id'] != $user['id']) {
            http_response_code(403);
            echo 'Access denied';
            exit;
        }
        
        $duplicates = $this->leadModel->findDuplicates($id);
        
        $this->view('leads/duplicates', [
            'lead' => $lead,
            'duplicates' => $duplicates
        ]);
    }
    
    // Merge duplicate leads
    public function mergeDuplicates($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$id) {
            $this->redirect('index.php?action=leads');
        }
        
        $lead = $this->leadModel->getById($id);
        if (!$lead) {
            $this->redirect('index.php?action=leads');
        }
        
        // Check permissions
        $user = auth_user();
        if ($user['role'] === 'sdr' && $lead['sdr_id'] != $user['id']) {
            http_response_code(403);
            echo 'Access denied';
            exit;
        }
        
        $duplicateIds = $_POST['duplicate_ids'] ?? [];
        if (empty($duplicateIds)) {
            $this->redirect("index.php?action=find_duplicates&id={$id}&error=" . urlencode('No duplicates selected for merging'));
        }
        
        try {
            $this->leadModel->mergeDuplicates($id, $duplicateIds);
            $this->redirect("index.php?action=lead_view&id={$id}&success=" . urlencode('Successfully merged ' . count($duplicateIds) . ' duplicate lead(s)'));
        } catch (Exception $e) {
            $this->redirect("index.php?action=find_duplicates&id={$id}&error=" . urlencode('Failed to merge duplicates: ' . $e->getMessage()));
        }
    }
    
    // New leads management page with specific columns
    public function leadsManagement() {
        $user = auth_user();
        $filters = [
            'sdr_id' => $_GET['sdr_id'] ?? '',
            'status' => $_GET['status'] ?? ''
        ];
        
        // Remove empty filters
        $filters = array_filter($filters);
        
        // Role-based filtering
        if ($user['role'] === 'sdr') {
            $filters['sdr_id'] = $user['sdr_id'] ?? $user['id'];
        }
        
        $page = (int)($_GET['page'] ?? 1);
        $limit = 100;
        $offset = ($page - 1) * $limit;
        
        $leads = $this->leadModel->getLeadsForManagement($limit, $offset, $filters);
        $total = $this->leadModel->countLeadsForManagement($filters);
        $totalPages = max(1, (int)ceil($total / $limit));
        
        // Get statuses for dropdown
        $statusModel = new StatusModel();
        $statuses = $statusModel->all();
        
        // Get users for SDR filter
        $users = $this->userModel->all();
        
        $this->view('leads/management', [
            'leads' => $leads,
            'statuses' => $statuses,
            'users' => $users,
            'filters' => $filters,
            'page' => $page,
            'total' => $total,
            'totalPages' => $totalPages,
            'limit' => $limit
        ]);
    }
    
    // Bulk update status
    public function bulkUpdateStatus() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('index.php?action=leads');
        }
        
        $user = auth_user();
        $leadIds = $_POST['lead_ids'] ?? '';
        $newStatus = trim($_POST['new_status'] ?? '');
        
        if (empty($leadIds) || empty($newStatus)) {
            $this->redirect('index.php?action=leads&error=' . urlencode('No leads selected or status not specified'));
        }
        
        $ids = array_filter(array_map('intval', explode(',', $leadIds)));
        if (empty($ids)) {
            $this->redirect('index.php?action=leads&error=' . urlencode('Invalid lead IDs provided'));
        }
        
        // Check permissions for each lead
        if ($user['role'] === 'sdr') {
            $userSdrId = $user['sdr_id'] ?? $user['id'];
            foreach ($ids as $leadId) {
                $lead = $this->leadModel->getById($leadId);
                if (!$lead || $lead['sdr_id'] != $userSdrId) {
                    $this->redirect('index.php?action=leads&error=' . urlencode('Access denied for one or more leads'));
                }
            }
        }
        
        try {
            $this->leadModel->bulkUpdateStatus($ids, $newStatus, $user['id']);
            $this->redirect('index.php?action=leads&success=' . urlencode("Successfully updated status for " . count($ids) . " lead(s)"));
        } catch (Exception $e) {
            $this->redirect('index.php?action=leads&error=' . urlencode('Failed to update status: ' . $e->getMessage()));
        }
    }
}
?>
