<?php
// app/controllers/StatusController.php
class StatusController extends Controller {
    protected $statusModel;
    
    public function __construct() {
        parent::__construct();
        $this->statusModel = new StatusModel();
    }
    
    // List all statuses (admin only)
    public function index() {
        require_role(['admin']);
        
        $statuses = $this->statusModel->all();
        
        $this->view('status/index', [
            'statuses' => $statuses
        ]);
    }
    
    // Show create form
    public function create() {
        require_role(['admin']);
        
        $this->view('status/form', [
            'status' => null,
            'action' => 'create'
        ]);
    }
    
    // Store new status
    public function store() {
        require_role(['admin']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('index.php?action=status_add');
        }
        
        $name = trim($_POST['name'] ?? '');
        
        if (empty($name)) {
            $this->view('status/form', [
                'status' => null,
                'action' => 'create',
                'error' => 'Status name is required'
            ]);
            return;
        }
        
        // Check if status name already exists
        if ($this->statusModel->nameExists($name)) {
            $this->view('status/form', [
                'status' => null,
                'action' => 'create',
                'error' => 'Status name already exists'
            ]);
            return;
        }
        
        try {
            $this->statusModel->create($name);
            $this->redirect('index.php?action=status_management&success=' . urlencode('Status created successfully'));
        } catch (Exception $e) {
            $this->view('status/form', [
                'status' => null,
                'action' => 'create',
                'error' => 'Failed to create status: ' . $e->getMessage()
            ]);
        }
    }
    
    // Show edit form
    public function edit($id) {
        require_role(['admin']);
        
        if (!$id) {
            $this->redirect('index.php?action=status_management');
        }
        
        $status = $this->statusModel->getById($id);
        if (!$status) {
            $this->redirect('index.php?action=status_management');
        }
        
        $this->view('status/form', [
            'status' => $status,
            'action' => 'edit'
        ]);
    }
    
    // Update status
    public function update($id) {
        require_role(['admin']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$id) {
            $this->redirect('index.php?action=status_management');
        }
        
        $status = $this->statusModel->getById($id);
        if (!$status) {
            $this->redirect('index.php?action=status_management');
        }
        
        $name = trim($_POST['name'] ?? '');
        
        if (empty($name)) {
            $this->view('status/form', [
                'status' => $status,
                'action' => 'edit',
                'error' => 'Status name is required'
            ]);
            return;
        }
        
        // Check if status name already exists (excluding current ID)
        if ($this->statusModel->nameExists($name, $id)) {
            $this->view('status/form', [
                'status' => $status,
                'action' => 'edit',
                'error' => 'Status name already exists'
            ]);
            return;
        }
        
        try {
            $this->statusModel->update($id, $name);
            $this->redirect('index.php?action=status_management&success=' . urlencode('Status updated successfully'));
        } catch (Exception $e) {
            $this->view('status/form', [
                'status' => $status,
                'action' => 'edit',
                'error' => 'Failed to update status: ' . $e->getMessage()
            ]);
        }
    }
    
    // Delete status
    public function delete($id) {
        require_role(['admin']);
        
        if (!$id) {
            $this->redirect('index.php?action=status_management');
        }
        
        $status = $this->statusModel->getById($id);
        if (!$status) {
            $this->redirect('index.php?action=status_management');
        }
        
        try {
            $this->statusModel->delete($id);
            $this->redirect('index.php?action=status_management&success=' . urlencode('Status deleted successfully'));
        } catch (Exception $e) {
            $this->redirect('index.php?action=status_management&error=' . urlencode('Failed to delete status: ' . $e->getMessage()));
        }
    }
    
    // Get all statuses as JSON (for AJAX requests)
    public function getStatuses() {
        $statuses = $this->statusModel->all();
        header('Content-Type: application/json');
        echo json_encode($statuses);
        exit;
    }
}
?>
