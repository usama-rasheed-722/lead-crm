<?php

class LeadSourceController {
    private $leadSourceModel;
    
    public function __construct() {
        $this->leadSourceModel = new LeadSourceModel();
    }
    
    // List all lead sources (Admin only)
    public function index() {
        $user = auth_user();
        
        if ($user['role'] !== 'admin') {
            $this->redirect('index.php?action=dashboard&error=' . urlencode('Access denied. Admin privileges required.'));
        }
        
        $leadSources = $this->leadSourceModel->getAllWithUsage();
        
        $this->view('lead_sources/index', [
            'leadSources' => $leadSources
        ]);
    }
    
    // Show create form (Admin only)
    public function create() {
        $user = auth_user();
        
        if ($user['role'] !== 'admin') {
            $this->redirect('index.php?action=dashboard&error=' . urlencode('Access denied. Admin privileges required.'));
        }
        
        $this->view('lead_sources/form', [
            'leadSource' => null,
            'action' => 'create'
        ]);
    }
    
    // Store new lead source (Admin only)
    public function store() {
        $user = auth_user();
        
        if ($user['role'] !== 'admin') {
            $this->redirect('index.php?action=dashboard&error=' . urlencode('Access denied. Admin privileges required.'));
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('index.php?action=lead_source_create');
        }
        
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $isActive = isset($_POST['is_active']) ? (bool)$_POST['is_active'] : true;
        
        // Validation
        if (empty($name)) {
            $this->redirect('index.php?action=lead_source_create&error=' . urlencode('Lead source name is required.'));
        }
        
        if (strlen($name) > 100) {
            $this->redirect('index.php?action=lead_source_create&error=' . urlencode('Lead source name must be 100 characters or less.'));
        }
        
        if ($this->leadSourceModel->nameExists($name)) {
            $this->redirect('index.php?action=lead_source_create&error=' . urlencode('A lead source with this name already exists.'));
        }
        
        try {
            $this->leadSourceModel->create($name, $description, $isActive);
            $this->redirect('index.php?action=lead_sources&success=' . urlencode('Lead source created successfully.'));
        } catch (Exception $e) {
            $this->redirect('index.php?action=lead_source_create&error=' . urlencode('Failed to create lead source: ' . $e->getMessage()));
        }
    }
    
    // Show edit form (Admin only)
    public function edit() {
        $user = auth_user();
        
        if ($user['role'] !== 'admin') {
            $this->redirect('index.php?action=dashboard&error=' . urlencode('Access denied. Admin privileges required.'));
        }
        
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            $this->redirect('index.php?action=lead_sources&error=' . urlencode('Lead source ID required.'));
        }
        
        $leadSource = $this->leadSourceModel->getById($id);
        
        if (!$leadSource) {
            $this->redirect('index.php?action=lead_sources&error=' . urlencode('Lead source not found.'));
        }
        
        $this->view('lead_sources/form', [
            'leadSource' => $leadSource,
            'action' => 'edit'
        ]);
    }
    
    // Update lead source (Admin only)
    public function update() {
        $user = auth_user();
        
        if ($user['role'] !== 'admin') {
            $this->redirect('index.php?action=dashboard&error=' . urlencode('Access denied. Admin privileges required.'));
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('index.php?action=lead_sources');
        }
        
        $id = $_POST['id'] ?? null;
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $isActive = isset($_POST['is_active']) ? (bool)$_POST['is_active'] : true;
        
        if (!$id) {
            $this->redirect('index.php?action=lead_sources&error=' . urlencode('Lead source ID required.'));
        }
        
        // Validation
        if (empty($name)) {
            $this->redirect('index.php?action=lead_source_edit&id=' . $id . '&error=' . urlencode('Lead source name is required.'));
        }
        
        if (strlen($name) > 100) {
            $this->redirect('index.php?action=lead_source_edit&id=' . $id . '&error=' . urlencode('Lead source name must be 100 characters or less.'));
        }
        
        if ($this->leadSourceModel->nameExists($name, $id)) {
            $this->redirect('index.php?action=lead_source_edit&id=' . $id . '&error=' . urlencode('A lead source with this name already exists.'));
        }
        
        try {
            $this->leadSourceModel->update($id, $name, $description, $isActive);
            $this->redirect('index.php?action=lead_sources&success=' . urlencode('Lead source updated successfully.'));
        } catch (Exception $e) {
            $this->redirect('index.php?action=lead_source_edit&id=' . $id . '&error=' . urlencode('Failed to update lead source: ' . $e->getMessage()));
        }
    }
    
    // Delete lead source (Admin only)
    public function delete() {
        $user = auth_user();
        
        if ($user['role'] !== 'admin') {
            $this->redirect('index.php?action=dashboard&error=' . urlencode('Access denied. Admin privileges required.'));
        }
        
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            $this->redirect('index.php?action=lead_sources&error=' . urlencode('Lead source ID required.'));
        }
        
        try {
            $this->leadSourceModel->delete($id);
            $this->redirect('index.php?action=lead_sources&success=' . urlencode('Lead source deleted successfully.'));
        } catch (Exception $e) {
            $this->redirect('index.php?action=lead_sources&error=' . urlencode('Failed to delete lead source: ' . $e->getMessage()));
        }
    }
    
    // Toggle active status (Admin only)
    public function toggleActive() {
        $user = auth_user();
        
        if ($user['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['error' => 'Access denied']);
            exit;
        }
        
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'Lead source ID required']);
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
    
    // Helper method to render views
    private function view($view, $data = []) {
        extract($data);
        include __DIR__ . "/../views/{$view}.php";
    }
    
    // Helper method to redirect
    private function redirect($url) {
        header("Location: {$url}");
        exit;
    }
}
?>
