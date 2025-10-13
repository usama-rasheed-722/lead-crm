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
            'date_to' => $_GET['date_to'] ?? ''
        ];
        
        // Remove empty filters
        $filters = array_filter($filters);
        
        $page = (int)($_GET['page'] ?? 1);
        $limit = 100;
        $offset = ($page - 1) * $limit;
        
        // Role-based filtering
        if ($user['role'] === 'sdr') {
            $filters['sdr_id'] = $user['id'];
        }
        
        $leads = $this->leadModel->search($search, $filters, $limit, $offset);
        $total = $this->leadModel->countSearch($search, $filters);
        $totalPages = max(1, (int)ceil($total / $limit));
        $users = $this->userModel->all();
        
        $this->view('leads/index', [
            'leads' => $leads,
            'users' => $users,
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
        if ($user['role'] === 'sdr' && $lead['sdr_id'] != $user['id']) {
            http_response_code(403);
            echo 'Access denied';
            exit;
        }
        
        // Get lead notes
        $noteModel = new NoteModel();
        $notes = $noteModel->getByLeadId($id);
        
        $this->view('leads/view', [
            'lead' => $lead,
            'notes' => $notes
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
            'lead_id' => trim($_POST['lead_id'] ?? generateNextSDR(empty($_POST['sdr_id'])?$user['id']:$_POST['sdr_id'])),
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
                ? (empty($_POST['sdr_id']) ? (int)$user['id'] : (int)$_POST['sdr_id'])
                : (int)$user['id'],
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
        if ($user['role'] === 'sdr' && $lead['sdr_id'] != $user['id']) {
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
        if ($user['role'] === 'sdr' && $lead['sdr_id'] != $user['id']) {
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
    
    // Bulk generate SDR numbers for all leads without them
    public function bulkGenerateSDR() {
        require_role(['admin']); // Only admins can do bulk operations
        
        try {
            $pdo = $this->pdo;
            $pdo->beginTransaction();
            
            // Get all leads without proper SDR numbers
            $stmt = $pdo->prepare('
                SELECT id, sdr_id 
                FROM leads 
                WHERE lead_id IS NULL 
                OR lead_id = "" 
                OR lead_id NOT REGEXP "^SDR[0-9]+-[0-9]+$"
                ORDER BY sdr_id, id
            ');
            $stmt->execute();
            $leads = $stmt->fetchAll();
            
            $generated = 0;
            foreach ($leads as $lead) {
                if ($lead['sdr_id']) {
                    $sdrNumber = generateNextSDR($lead['sdr_id']);
                    $updateStmt = $pdo->prepare('UPDATE leads SET lead_id = ? WHERE id = ?');
                    $updateStmt->execute([$sdrNumber, $lead['id']]);
                    $generated++;
                }
            }
            
            $pdo->commit();
            $this->redirect("index.php?action=leads&success=" . urlencode("Generated {$generated} SDR numbers successfully"));
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $this->redirect("index.php?action=leads&error=" . urlencode('Failed to generate SDR numbers: ' . $e->getMessage()));
        }
    }
}
?>
