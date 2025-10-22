<?php
// app/controllers/LeadSourceController.php
class LeadSourceController extends Controller {
    protected $leadSourceModel;
    
    public function __construct() {
        parent::__construct();
        $this->leadSourceModel = new LeadSourceModel();
    }
    
    // List all lead sources (Admin only)
    public function index() {
        require_role(['admin']);
        
        $leadSources = $this->leadSourceModel->getAllWithUsage();
        
        $this->view('lead_sources/index', [
            'leadSources' => $leadSources
        ]);
    }
    
    // Show create form (Admin only)
    public function create() {
        require_role(['admin']);
        
        $this->view('lead_sources/form', [
            'leadSource' => null,
            'action' => 'create'
        ]);
    }
    
    // Store new lead source (Admin only)
    public function store() {
        require_role(['admin']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('index.php?action=lead_source_create');
        }
        
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $isActive = isset($_POST['is_active']) ? (bool)$_POST['is_active'] : true;
        
        // Validation
        if (empty($name)) {
            $this->view('lead_sources/form', [
                'leadSource' => null,
                'action' => 'create',
                'error' => 'Lead source name is required.'
            ]);
            return;
        }
        
        if (strlen($name) > 100) {
            $this->view('lead_sources/form', [
                'leadSource' => null,
                'action' => 'create',
                'error' => 'Lead source name must be 100 characters or less.'
            ]);
            return;
        }
        
        if ($this->leadSourceModel->nameExists($name)) {
            $this->view('lead_sources/form', [
                'leadSource' => null,
                'action' => 'create',
                'error' => 'A lead source with this name already exists.'
            ]);
            return;
        }
        
        try {
            $this->leadSourceModel->create($name, $description, $isActive);
            $this->redirect('index.php?action=lead_sources&success=' . urlencode('Lead source created successfully.'));
        } catch (Exception $e) {
            $this->view('lead_sources/form', [
                'leadSource' => null,
                'action' => 'create',
                'error' => 'Failed to create lead source: ' . $e->getMessage()
            ]);
        }
    }
    
    // Show edit form (Admin only)
    public function edit($id) {
        require_role(['admin']);
        
        if (!$id) {
            $this->redirect('index.php?action=lead_sources');
        }
        
        $leadSource = $this->leadSourceModel->getById($id);
        if (!$leadSource) {
            $this->redirect('index.php?action=lead_sources');
        }
        
        $this->view('lead_sources/form', [
            'leadSource' => $leadSource,
            'action' => 'edit'
        ]);
    }
    
    // Update lead source (Admin only)
    public function update($id) {
        require_role(['admin']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$id) {
            $this->redirect('index.php?action=lead_sources');
        }
        
        $leadSource = $this->leadSourceModel->getById($id);
        if (!$leadSource) {
            $this->redirect('index.php?action=lead_sources');
        }
        
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $isActive = isset($_POST['is_active']) ? (bool)$_POST['is_active'] : true;
        
        // Validation
        if (empty($name)) {
            $this->view('lead_sources/form', [
                'leadSource' => $leadSource,
                'action' => 'edit',
                'error' => 'Lead source name is required.'
            ]);
            return;
        }
        
        if (strlen($name) > 100) {
            $this->view('lead_sources/form', [
                'leadSource' => $leadSource,
                'action' => 'edit',
                'error' => 'Lead source name must be 100 characters or less.'
            ]);
            return;
        }
        
        if ($this->leadSourceModel->nameExists($name, $id)) {
            $this->view('lead_sources/form', [
                'leadSource' => $leadSource,
                'action' => 'edit',
                'error' => 'A lead source with this name already exists.'
            ]);
            return;
        }
        
        try {
            $this->leadSourceModel->update($id, $name, $description, $isActive);
            $this->redirect('index.php?action=lead_sources&success=' . urlencode('Lead source updated successfully.'));
        } catch (Exception $e) {
            $this->view('lead_sources/form', [
                'leadSource' => $leadSource,
                'action' => 'edit',
                'error' => 'Failed to update lead source: ' . $e->getMessage()
            ]);
        }
    }
    
    // Delete lead source (Admin only)
    public function delete($id) {
        require_role(['admin']);
        
        if (!$id) {
            $this->redirect('index.php?action=lead_sources');
        }
        
        $leadSource = $this->leadSourceModel->getById($id);
        if (!$leadSource) {
            $this->redirect('index.php?action=lead_sources');
        }
        
        try {
            $this->leadSourceModel->delete($id);
            $this->redirect('index.php?action=lead_sources&success=' . urlencode('Lead source deleted successfully.'));
        } catch (Exception $e) {
            $this->redirect('index.php?action=lead_sources&error=' . urlencode('Failed to delete lead source: ' . $e->getMessage()));
        }
    }
    
    // Toggle active status (Admin only)
    public function toggleActive($id) {
        require_role(['admin']);
        
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'Lead source ID required']);
            exit;
        }
        
        $leadSource = $this->leadSourceModel->getById($id);
        if (!$leadSource) {
            http_response_code(404);
            echo json_encode(['error' => 'Lead source not found']);
            exit;
        }
        
        try {
            $this->leadSourceModel->toggleActive($id);
            $leadSource = $this->leadSourceModel->getById($id);
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'is_active' => (bool)$leadSource['is_active']
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    }
    
    // Get all lead sources as JSON (for AJAX requests)
    public function getLeadSources() {
        $leadSources = $this->leadSourceModel->getActive();
        header('Content-Type: application/json');
        echo json_encode($leadSources);
        exit;
    }
}
?>
