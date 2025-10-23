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
        $restrictBulkUpdate = isset($_POST['restrict_bulk_update']);
        $isDefault = isset($_POST['is_default']);
        
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
            $this->statusModel->create($name, $restrictBulkUpdate, $isDefault);
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
        
        // Get custom fields for this status
        $customFields = $this->statusModel->getCustomFields($id);
        
        $this->view('status/form', [
            'status' => $status,
            'customFields' => $customFields,
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
        $restrictBulkUpdate = isset($_POST['restrict_bulk_update']);
        $isDefault = isset($_POST['is_default']);
        
        if (empty($name)) {
            $this->view('status/form', [
                'status' => $status,
                'customFields' => $this->statusModel->getCustomFields($id),
                'action' => 'edit',
                'error' => 'Status name is required'
            ]);
            return;
        }
        
        // Check if status name already exists (excluding current ID)
        if ($this->statusModel->nameExists($name, $id)) {
            $this->view('status/form', [
                'status' => $status,
                'customFields' => $this->statusModel->getCustomFields($id),
                'action' => 'edit',
                'error' => 'Status name already exists'
            ]);
            return;
        }
        
        try {
            $this->statusModel->update($id, $name, $restrictBulkUpdate, $isDefault);
            $this->redirect('index.php?action=status_management&success=' . urlencode('Status updated successfully'));
        } catch (Exception $e) {
            $this->view('status/form', [
                'status' => $status,
                'customFields' => $this->statusModel->getCustomFields($id),
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
    
    // Create custom field
    public function createCustomField() {
        require_role(['admin']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('index.php?action=status_management');
        }
        
        $statusId = (int)($_POST['status_id'] ?? 0);
        $fieldName = trim($_POST['field_name'] ?? '');
        $fieldLabel = trim($_POST['field_label'] ?? '');
        $fieldType = trim($_POST['field_type'] ?? 'text');
        $fieldOptions = trim($_POST['field_options'] ?? '');
        $isRequired = isset($_POST['is_required']);
        $fieldOrder = (int)($_POST['field_order'] ?? 0);
        
        if (empty($statusId) || empty($fieldName) || empty($fieldLabel)) {
            $this->redirect("index.php?action=status_edit&id={$statusId}&error=" . urlencode('Field name and label are required'));
        }
        
        try {
            $this->statusModel->createCustomField($statusId, $fieldName, $fieldLabel, $fieldType, $fieldOptions, $isRequired, $fieldOrder);
            $this->redirect("index.php?action=status_edit&id={$statusId}&success=" . urlencode('Custom field created successfully'));
        } catch (Exception $e) {
            $this->redirect("index.php?action=status_edit&id={$statusId}&error=" . urlencode('Failed to create custom field: ' . $e->getMessage()));
        }
    }
    
    // Update custom field
    public function updateCustomField($fieldId) {
        require_role(['admin']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$fieldId) {
            $this->redirect('index.php?action=status_management');
        }
        
        $field = $this->statusModel->getCustomFieldById($fieldId);
        if (!$field) {
            $this->redirect('index.php?action=status_management');
        }
        
        $fieldName = trim($_POST['field_name'] ?? '');
        $fieldLabel = trim($_POST['field_label'] ?? '');
        $fieldType = trim($_POST['field_type'] ?? 'text');
        $fieldOptions = trim($_POST['field_options'] ?? '');
        $isRequired = isset($_POST['is_required']);
        $fieldOrder = (int)($_POST['field_order'] ?? 0);
        
        if (empty($fieldName) || empty($fieldLabel)) {
            $this->redirect("index.php?action=status_edit&id={$field['status_id']}&error=" . urlencode('Field name and label are required'));
        }
        
        try {
            $this->statusModel->updateCustomField($fieldId, $fieldName, $fieldLabel, $fieldType, $fieldOptions, $isRequired, $fieldOrder);
            $this->redirect("index.php?action=status_edit&id={$field['status_id']}&success=" . urlencode('Custom field updated successfully'));
        } catch (Exception $e) {
            $this->redirect("index.php?action=status_edit&id={$field['status_id']}&error=" . urlencode('Failed to update custom field: ' . $e->getMessage()));
        }
    }
    
    // Delete custom field
    public function deleteCustomField($fieldId) {
        require_role(['admin']);
        
        if (!$fieldId) {
            $this->redirect('index.php?action=status_management');
        }
        
        $field = $this->statusModel->getCustomFieldById($fieldId);
        if (!$field) {
            $this->redirect('index.php?action=status_management');
        }
        
        try {
            $this->statusModel->deleteCustomField($fieldId);
            $this->redirect("index.php?action=status_edit&id={$field['status_id']}&success=" . urlencode('Custom field deleted successfully'));
        } catch (Exception $e) {
            $this->redirect("index.php?action=status_edit&id={$field['status_id']}&error=" . urlencode('Failed to delete custom field: ' . $e->getMessage()));
        }
    }
    
    // Set status as default (AJAX endpoint)
    public function setAsDefault($id) {
        require_role(['admin']);
        
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'Status ID required']);
            exit;
        }
        
        $status = $this->statusModel->getById($id);
        if (!$status) {
            http_response_code(404);
            echo json_encode(['error' => 'Status not found']);
            exit;
        }
        
        try {
            $this->statusModel->setAsDefault($id);
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Status set as default successfully']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to set status as default: ' . $e->getMessage()]);
        }
        exit;
    }

    // Update sequence for a status
    public function updateSequence($id) {
        require_role(['admin']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$id) {
            $this->redirect('index.php?action=status_management');
        }
        
        $sequence = (int)($_POST['sequence'] ?? 0);
        
        try {
            $this->statusModel->updateSequence($id, $sequence);
            $this->redirect('index.php?action=status_management&success=' . urlencode('Status sequence updated successfully'));
        } catch (Exception $e) {
            $this->redirect('index.php?action=status_management&error=' . urlencode('Failed to update sequence: ' . $e->getMessage()));
        }
    }

    // Bulk update sequences
    public function updateSequences() {
        require_role(['admin']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('index.php?action=status_management');
        }
        
        $sequences = $_POST['sequences'] ?? [];
        
        if (empty($sequences)) {
            $this->redirect('index.php?action=status_management&error=' . urlencode('No sequences provided'));
        }
        
        try {
            $this->statusModel->updateSequences($sequences);
            $this->redirect('index.php?action=status_management&success=' . urlencode('Status sequences updated successfully'));
        } catch (Exception $e) {
            $this->redirect('index.php?action=status_management&error=' . urlencode('Failed to update sequences: ' . $e->getMessage()));
        }
    }
}
?>
