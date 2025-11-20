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
    
    /**
     * Check if SDR has access to a lead
     * Priority 1: Check if lead is assigned to user (assigned_to column)
     * Priority 2: Check if user is the owner (sdr_id matches)
     */
    private function canAccessLead($lead, $user) {
        if ($user['role'] !== 'sdr') {
            return true; // Admins and managers have access
        }
        
        // Priority 1: Check if lead is assigned to this user
        if (!empty($lead['assigned_to']) && (string)$lead['assigned_to'] === (string)$user['id']) {
            return true;
        }
        
        // Priority 2: Check if user is the owner
        $userSdrId = $user['sdr_id'] ?? $user['id'];
        if ((string)$lead['sdr_id'] === (string)$userSdrId) {
            return true;
        }
        
        return false;
    }
    
    // List all leads with search and filters
    public function index() {
        $user = auth_user();
        
        // Handle search parameter (separate from filters)
        if (isset($_GET['search'])) {
            $_SESSION['filters']['leads_search'] = $_GET['search'];
            $search = $_GET['search'];
        } elseif (isset($_SESSION['filters']['leads_search'])) {
            $search = $_SESSION['filters']['leads_search'];
        } else {
            $search = '';
        }
        
        // Get filters from session or GET parameters
        $defaultFilters = [
            'sdr_id' => '',
            'duplicate_status' => '',
            'date_from' => '',
            'date_to' => '',
            'lead_source_id' => '',
            'status_id' => '',
            'field_type' => '',
            'field_value' => ''
        ];
        
        $filters = $this->getFilters('leads', $defaultFilters);
        
        // Remove empty filters for query building
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
        
        // Load lead sources for filters
        $leadSourceModel = new LeadSourceModel();
        $leadSources = $leadSourceModel->getActive();
        
        // Get available fields from leads table
        $availableFields = $this->leadModel->getAvailableFields();
        
        $this->view('leads/index', [
            'leads' => $leads,
            'users' => $users,
            'statuses' => $statuses,
            'leadSources' => $leadSources,
            'availableFields' => $availableFields,
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
        if (!$this->canAccessLead($lead, $user)) {
            http_response_code(403);
            echo 'Access denied';
            exit;
        }
        
        // Get lead notes
        $noteModel = new NoteModel();
        $notes = $noteModel->getByLeadId($id);

        // Get status history
        $statusHistory = $this->leadModel->getStatusHistory($id);
        
        $this->view('leads/view', [
            'lead' => $lead,
            'notes' => $notes,
            'statusHistory' => $statusHistory
        ]);
    }
    
    // Show create form
    public function create() {
        $users = $this->userModel->all();
        $leadSourceModel = new LeadSourceModel();
        $leadSources = $leadSourceModel->getActive();
        $statusModel = new StatusModel();
        $statuses = $statusModel->all();
        $this->view('leads/form', [
            'lead' => null,
            'users' => $users,
            'leadSources' => $leadSources,
            'statuses' => $statuses,
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
            'lead_source_id' => (int)($_POST['lead_source_id'] ?? 0),
            'tier' => trim($_POST['tier'] ?? ''),
            'lead_status' => trim($_POST['lead_status'] ?? ''),
            'insta' => trim($_POST['insta'] ?? ''),
            'social_profile' => trim($_POST['social_profile'] ?? ''),
            'address' => trim($_POST['address'] ?? ''),
            'description_information' => trim($_POST['description_information'] ?? ''),
            'whatsapp' => trim($_POST['whatsapp'] ?? ''),
            'next_step' => trim($_POST['next_step'] ?? ''),
            'other' => trim($_POST['other'] ?? ''),
            'status_id' => (int)($_POST['status_id'] ?? 0),
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
            $leadSourceModel = new LeadSourceModel();
            $leadSources = $leadSourceModel->getActive();
            $statusModel = new StatusModel();
            $statuses = $statusModel->all();
            $this->view('leads/form', [
                'lead' => $data,
                'users' => $users,
                'leadSources' => $leadSources,
                'statuses' => $statuses,
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
        if (!$this->canAccessLead($lead, $user)) {
            http_response_code(403);
            echo 'Access denied';
            exit;
        }
        
        $users = $this->userModel->all();
        $leadSourceModel = new LeadSourceModel();
        $leadSources = $leadSourceModel->getActive();
        $statusModel = new StatusModel();
        $statuses = $statusModel->all();
        $this->view('leads/form', [
            'lead' => $lead,
            'users' => $users,
            'leadSources' => $leadSources,
            'statuses' => $statuses,
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
        if (!$this->canAccessLead($lead, $user)) {
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
            'lead_source_id' => (int)($_POST['lead_source_id'] ?? 0),
            'tier' => trim($_POST['tier'] ?? ''),
            'lead_status' => trim($_POST['lead_status'] ?? ''),
            'insta' => trim($_POST['insta'] ?? ''),
            'social_profile' => trim($_POST['social_profile'] ?? ''),
            'address' => trim($_POST['address'] ?? ''),
            'description_information' => trim($_POST['description_information'] ?? ''),
            'whatsapp' => trim($_POST['whatsapp'] ?? ''),
            'next_step' => trim($_POST['next_step'] ?? ''),
            'other' => trim($_POST['other'] ?? ''),
            'status_id' => (int)($_POST['status_id'] ?? 0),
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
            $leadSourceModel = new LeadSourceModel();
            $leadSources = $leadSourceModel->getActive();
            $statusModel = new StatusModel();
            $statuses = $statusModel->all();
            $this->view('leads/form', [
                'lead' => array_merge($lead, $data),
                'users' => $users,
                'leadSources' => $leadSources,
                'statuses' => $statuses,
                'action' => 'edit',
                'error' => $error
            ]);
        }
    }
    
    // Delete lead (soft delete)
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
        if (!$this->canAccessLead($lead, $user)) {
            http_response_code(403);
            echo 'Access denied';
            exit;
        }
        
        try {
            $this->leadModel->delete($id);
            $this->redirect('index.php?action=leads', ['success' => 'Lead moved to trash']);
        } catch (Exception $e) {
            $this->redirect('index.php?action=leads', ['error' => 'Failed to delete lead']);
        }
    }
    
    // View trash (admin only)
    public function trash() {
        require_role(['admin']);
        
        $page = (int)($_GET['page'] ?? 1);
        $limit = 100;
        $offset = ($page - 1) * $limit;
        
        $deletedLeads = $this->leadModel->getDeletedLeads($limit, $offset);
        $total = $this->leadModel->countDeletedLeads();
        $totalPages = max(1, (int)ceil($total / $limit));
        
        $this->view('leads/trash', [
            'leads' => $deletedLeads,
            'page' => $page,
            'total' => $total,
            'totalPages' => $totalPages,
            'limit' => $limit
        ]);
    }
    
    // Restore deleted lead (admin only)
    public function restoreLead($id) {
        require_role(['admin']);
        
        if (!$id) {
            $this->redirect('index.php?action=trash');
        }
        
        try {
            $this->leadModel->restore($id);
            $this->redirect('index.php?action=trash', ['success' => 'Lead restored successfully']);
        } catch (Exception $e) {
            $this->redirect('index.php?action=trash', ['error' => 'Failed to restore lead']);
        }
    }
    
    // Permanently delete lead (admin only)
    public function permanentDelete($id) {
        require_role(['admin']);
        
        if (!$id) {
            $this->redirect('index.php?action=trash');
        }
        
        try {
            $this->leadModel->hardDelete($id);
            $this->redirect('index.php?action=trash', ['success' => 'Lead permanently deleted']);
        } catch (Exception $e) {
            $this->redirect('index.php?action=trash', ['error' => 'Failed to permanently delete lead']);
        }
    }
    
    // Bulk permanent delete (admin only)
    public function bulkPermanentDelete() {
        require_role(['admin']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('index.php?action=trash');
        }
        
        $leadIds = $_POST['lead_ids'] ?? '';
        if (empty($leadIds)) {
            $this->redirect('index.php?action=trash', ['error' => 'No leads selected for deletion']);
        }
        
        $ids = array_filter(array_map('intval', explode(',', $leadIds)));
        if (empty($ids)) {
            $this->redirect('index.php?action=trash', ['error' => 'Invalid lead IDs provided']);
        }
        
        try {
            $pdo = $this->pdo;
            $pdo->beginTransaction();
            
            $placeholders = str_repeat('?,', count($ids) - 1) . '?';
            $stmt = $pdo->prepare("DELETE FROM leads WHERE id IN ($placeholders)");
            $stmt->execute($ids);
            
            $deletedCount = $stmt->rowCount();
            $pdo->commit();
            
            $this->redirect("index.php?action=trash", ['success' => "Successfully permanently deleted {$deletedCount} lead(s)"]);
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $this->redirect("index.php?action=trash", ['error' => 'Failed to permanently delete leads: ' . $e->getMessage()]);
        }
    }
    
    // Bulk restore (admin only)
    public function bulkRestore() {
        require_role(['admin']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('index.php?action=trash');
        }
        
        $leadIds = $_POST['lead_ids'] ?? '';
        if (empty($leadIds)) {
            $this->redirect('index.php?action=trash', ['error' => 'No leads selected for restoration']);
        }
        
        $ids = array_filter(array_map('intval', explode(',', $leadIds)));
        if (empty($ids)) {
            $this->redirect('index.php?action=trash', ['error' => 'Invalid lead IDs provided']);
        }
        
        try {
            $pdo = $this->pdo;
            $pdo->beginTransaction();
            
            $placeholders = str_repeat('?,', count($ids) - 1) . '?';
            $stmt = $pdo->prepare("UPDATE leads SET deleted_at = NULL WHERE id IN ($placeholders)");
            $stmt->execute($ids);
            
            $restoredCount = $stmt->rowCount();
            $pdo->commit();
            
            $this->redirect("index.php?action=trash", ['success' => "Successfully restored {$restoredCount} lead(s)"]);
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $this->redirect("index.php?action=trash", ['error' => 'Failed to restore leads: ' . $e->getMessage()]);
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
        if (!$this->canAccessLead($lead, $user)) {
            http_response_code(403);
            echo 'Access denied';
            exit;
        }
        
        try {
            $sdrNumber = generateSDRNumber($id, $lead['sdr_id']);
            $this->redirect("index.php?action=lead_view&id={$id}", ['success' => "SDR number generated: {$sdrNumber}"]);
        } catch (Exception $e) {
            $this->redirect("index.php?action=lead_view&id={$id}", ['error' => 'Failed to generate SDR number']);
        }
    }
    
    public function bulkDelete() {
        require_role(['admin','sdr','manager']); // Only admins can do bulk operations
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('index.php?action=leads');
        }
        
        $leadIds = $_POST['lead_ids'] ?? '';
        if (empty($leadIds)) {
            $this->redirect('index.php?action=leads', ['error' => 'No leads selected for deletion']);
        }
        
        $ids = array_filter(array_map('intval', explode(',', $leadIds)));
        if (empty($ids)) {
            $this->redirect('index.php?action=leads', ['error' => 'Invalid lead IDs provided']);
        }
        
        try {
            $pdo = $this->pdo;
            $pdo->beginTransaction();
            
            $placeholders = str_repeat('?,', count($ids) - 1) . '?';
            $stmt = $pdo->prepare("UPDATE leads SET deleted_at = NOW() WHERE id IN ($placeholders)");
            $stmt->execute($ids);
            
            $deletedCount = $stmt->rowCount();
            $pdo->commit();
            
            $this->redirect("index.php?action=leads", ['success' => "Successfully deleted {$deletedCount} lead(s)"]);
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $this->redirect("index.php?action=leads", ['error' => 'Failed to delete leads: ' . $e->getMessage()]);
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
        if (!$this->canAccessLead($lead, $user)) {
            http_response_code(403);
            echo 'Access denied';
            exit;
        }
        
        $currentSdrId = $user['sdr_id'] ?? $user['id'];
        $duplicates = $this->leadModel->findDuplicates($id);
        // For SDRs, only show duplicates that belong to them
        if ($user['role'] === 'sdr') {
            $duplicates = array_values(array_filter($duplicates, function($dup) use ($currentSdrId) {
                return (string)($dup['sdr_id'] ?? '') === (string)$currentSdrId;
            }));
        }
        
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
        if (!$this->canAccessLead($lead, $user)) {
            http_response_code(403);
            echo 'Access denied';
            exit;
        }
        
        $currentSdrId = $user['sdr_id'] ?? $user['id'];
        $duplicateIds = $_POST['duplicate_ids'] ?? [];
        if (empty($duplicateIds)) {
            $this->redirect("index.php?action=find_duplicates&id={$id}", ['error' => 'No duplicates selected for merging']);
        }
        
        try {
            // If SDR, ensure selected duplicates also belong to the same SDR
            if ($user['role'] === 'sdr') {
                foreach ($duplicateIds as $dupId) {
                    $dupLead = $this->leadModel->getById($dupId);
                    if (!$dupLead || (string)$dupLead['sdr_id'] !== (string)$currentSdrId) {
                        $this->redirect("index.php?action=find_duplicates&id={$id}", ['error' => 'You can only merge duplicates that belong to you']);
                    }
                }
            }

            // Pass SDR id so the primary lead retains/gets assigned to the merging SDR
            $this->leadModel->mergeDuplicates($id, $duplicateIds, $currentSdrId);
            $this->redirect("index.php?action=lead_view&id={$id}", ['success' => 'Successfully merged ' . count($duplicateIds) . ' duplicate lead(s)']);
        } catch (Exception $e) {
            $this->redirect("index.php?action=find_duplicates&id={$id}", ['error' => 'Failed to merge duplicates: ' . $e->getMessage()]);
        }
    }
    
    // New leads management page with specific columns
    public function leadsManagement() {
        $user = auth_user();
        
        // Get filters from session or GET parameters
        $defaultFilters = [
            'sdr_id' => '',
            'status_id' => ''
        ];
        
        $filters = $this->getFilters('leads_management', $defaultFilters);
        
        // Remove empty filters for query building
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
        $newStatusId = (int)($_POST['new_status_id'] ?? 0);
        
        if (empty($leadIds) || empty($newStatusId)) {
            $this->redirect('index.php?action=leads', ['error' => 'No leads selected or status not specified']);
        }
        
        $ids = array_filter(array_map('intval', explode(',', $leadIds)));
        if (empty($ids)) {
            $this->redirect('index.php?action=leads', ['error' => 'Invalid lead IDs provided']);
        }
        
        // Get the status name for checking restrictions
        $statusModel = new StatusModel();
        $newStatus = $statusModel->getById($newStatusId);
        if (!$newStatus) {
            $this->redirect('index.php?action=leads', ['error' => 'Invalid status selected']);
        }
        
        // Check if the target status restricts bulk updates
        if ($statusModel->restrictsBulkUpdateByName($newStatus['name'])) {
            $this->redirect('index.php?action=leads', ['error' => 'Bulk status update is not allowed for the selected status. Please update leads individually.']);
        }
        
        // Check permissions for each lead
        if ($user['role'] === 'sdr') {
            foreach ($ids as $leadId) {
                $lead = $this->leadModel->getById($leadId);
                if (!$lead || !$this->canAccessLead($lead, $user)) {
                    $this->redirect('index.php?action=leads', ['error' => 'Access denied for one or more leads. You can only update leads assigned to you.']);
                }
            }
        }
        // Admins and managers can update any lead
        
        // Get custom fields for the new status
        $customFields = $statusModel->getCustomFieldsByName($newStatus['name']);
        
        // Collect custom fields data
        $customFieldsData = [];
        foreach ($customFields as $field) {
            $fieldName = $field['field_name'];
            $fieldValue = $_POST['custom_field_' . $fieldName] ?? '';
            
            // Validate required fields
            if ($field['is_required'] && empty($fieldValue)) {
                $this->redirect('index.php?action=leads', ['error' => "Field '{$field['field_label']}' is required"]);
            }
            
            $customFieldsData[$fieldName] = $fieldValue;
        }
        
        try {
            // Use the new bulk update method with single transaction and single query
            $this->leadModel->bulkUpdateStatusWithCustomFields($ids, $newStatusId, $user['id'], $customFieldsData);
            $this->redirect('index.php?action=leads', ['success' => "Successfully updated status for " . count($ids) . " lead(s)"]);
        } catch (Exception $e) {
            $this->redirect('index.php?action=leads', ['error' => 'Failed to update status: ' . $e->getMessage()]);
        }
    }

    // Update status with custom fields
    public function updateStatusWithCustomFields() {
        // dd($_POST,1);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('index.php?action=leads');
        }
        $user = auth_user();
        $leadId = (int)($_POST['lead_id'] ?? 0);
        $newStatusId = (int)($_POST['new_status_id'] ?? 0);
        
        if (empty($leadId) || empty($newStatusId)) {
            $this->redirect('index.php?action=leads', ['error' => 'Invalid lead ID or status not specified']);
        }
        
        // Check permissions
        $lead = $this->leadModel->getById($leadId);
        if (!$lead) {
            $this->redirect('index.php?action=leads', ['error' => 'Lead not found']);
        }
        
        if (!$this->canAccessLead($lead, $user)) {
            $this->redirect('index.php?action=leads', ['error' => 'Access denied']);
        }
        
        // Get custom fields for the new status
        $statusModel = new StatusModel();
        $newStatus = $statusModel->getById($newStatusId);
        $customFields = $statusModel->getCustomFieldsByName($newStatus['name']);
        
        // Collect custom fields data
        $customFieldsData = [];
        foreach ($customFields as $field) {
            $fieldName = $field['field_name'];
            $fieldValue = $_POST['custom_field_' . $fieldName] ?? '';
            
            // Validate required fields
            if ($field['is_required'] && empty($fieldValue)) {
                $this->redirect("index.php?action=lead_view&id={$leadId}", ['error' => "Field '{$field['field_label']}' is required"]);
            }
            
            $customFieldsData[$fieldName] = $fieldValue;
        }
        
        try {
            $this->leadModel->updateStatusWithCustomFields($leadId, $newStatusId, $user['id'], $customFieldsData);
            $this->redirect("index.php?action=lead_status_history&id={$leadId}", ['success' => 'Status updated successfully']);
        } catch (Exception $e) {
            $this->redirect("index.php?action=lead_status_history&id={$leadId}", ['error' => 'Failed to update status: ' . $e->getMessage()]);
        }
    }

    // Get custom fields for a status (AJAX endpoint)
    public function getCustomFieldsForStatus() {
        $statusId = $_GET['status_id'] ?? '';
        
        if (empty($statusId)) {
            http_response_code(400);
            echo json_encode(['error' => 'Status ID required']);
            exit;
        }
        
        $statusModel = new StatusModel();
        $status = $statusModel->getById($statusId);
        
        if (!$status) {
            http_response_code(404);
            echo json_encode(['error' => 'Status not found']);
            exit;
        }
        
        $customFields = $statusModel->getCustomFieldsByName($status['name']);
        
        header('Content-Type: application/json');
        echo json_encode(['customFields' => $customFields]);
        exit;
    }

    // Bulk update status with custom fields
    public function bulkUpdateStatusWithCustomFields() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('index.php?action=leads');
        }
        
        $user = auth_user();
        $leadIds = $_POST['lead_ids'] ?? '';
        $newStatus = trim($_POST['new_status'] ?? '');
        
        if (empty($leadIds) || empty($newStatus)) {
            $this->redirect('index.php?action=leads', ['error' => 'No leads selected or status not specified']);
        }
        
        $ids = array_filter(array_map('intval', explode(',', $leadIds)));
        if (empty($ids)) {
            $this->redirect('index.php?action=leads', ['error' => 'Invalid lead IDs provided']);
        }
        
        // Check if the target status restricts bulk updates
        $statusModel = new StatusModel();
        if ($statusModel->restrictsBulkUpdateByName($newStatus)) {
            $this->redirect('index.php?action=leads', ['error' => 'Bulk status update is not allowed for the selected status. Please update leads individually.']);
        }
        
        // Check permissions for each lead
        if ($user['role'] === 'sdr') {
            foreach ($ids as $leadId) {
                $lead = $this->leadModel->getById($leadId);
                if (!$lead || !$this->canAccessLead($lead, $user)) {
                    $this->redirect('index.php?action=leads', ['error' => 'Access denied for one or more leads. You can only update leads assigned to you.']);
                }
            }
        }
        // Admins and managers can update any lead
        
        // Get custom fields for the new status
        $customFields = $statusModel->getCustomFieldsByName($newStatus);
        
        // Collect custom fields data
        $customFieldsData = [];
        foreach ($customFields as $field) {
            $fieldName = $field['field_name'];
            $fieldValue = $_POST['custom_field_' . $fieldName] ?? '';
            
            // Validate required fields
            if ($field['is_required'] && empty($fieldValue)) {
                $this->redirect('index.php?action=leads', ['error' => "Field '{$field['field_label']}' is required"]);
            }
            
            $customFieldsData[$fieldName] = $fieldValue;
        }
        
        try {
            // Update each lead individually with custom fields data
            $this->pdo->beginTransaction();
            
            foreach ($ids as $leadId) {
                $this->leadModel->updateStatusWithCustomFields($leadId, $newStatus, $user['id'], $customFieldsData);
            }
            
            $this->pdo->commit();
            $this->redirect('index.php?action=leads', ['success' => "Successfully updated status for " . count($ids) . " lead(s)"]);
        } catch (Exception $e) {
            $this->pdo->rollBack();
            $this->redirect('index.php?action=leads', ['error' => 'Failed to update status: ' . $e->getMessage()]);
        }
    }

    // Reset filters for leads page
    public function resetLeadsFilters() {
        $this->resetFilters('leads');
        if (isset($_SESSION['filters']['leads_search'])) {
            unset($_SESSION['filters']['leads_search']);
        }
        $this->redirect('index.php?action=leads');
    }
    
    // Reset filters for leads management page
    public function resetLeadsManagementFilters() {
        $this->resetFilters('leads_management');
        $this->redirect('index.php?action=leads_management');
    }
    
    // View full page status history for a lead
    public function statusHistory() {
        $leadId = $_GET['id'] ?? null;
        
        if (!$leadId) {
            $this->redirect('index.php?action=leads', ['error' => 'Lead ID required']);
        }
        
        $user = auth_user();
        
        // Get lead details first
        $lead = $this->leadModel->getById($leadId);
        if (!$lead) {
            $this->redirect('index.php?action=leads', ['error' => 'Lead not found']);
        }
        
        // Check permissions
        if (!$this->canAccessLead($lead, $user)) {
            $this->redirect('index.php?action=leads', ['error' => 'Access denied']);
        }
        
        // Get status history
        $statusHistory = $this->leadModel->getStatusHistory($leadId);
        
        // Get all statuses for reference
        $statusModel = new StatusModel();
        $statuses = $statusModel->all();
        
        $this->view('leads/status_history', [
            'lead' => $lead,
            'statusHistory' => $statusHistory,
            'statuses' => $statuses
        ]);
    }

    /**
     * Toggle field verification status
     * Accepts: POST request with lead_id and field name
     * Returns: JSON response with new verification status
     */
    public function toggleFieldVerification() {
        // Clear any output buffers and set JSON header
        while (ob_get_level()) {
            ob_end_clean();
        }
        header('Content-Type: application/json');
        
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Invalid request method']);
                exit;
            }
            
            $leadId = $_POST['lead_id'] ?? null;
            $field = $_POST['field'] ?? null;
            
            if (!$leadId || !$field) {
                echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
                exit;
            }
            
            $leadModel = new LeadModel();
            $result = $leadModel->toggleFieldVerification($leadId, $field);
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => ucfirst($field) . ' verification status updated',
                    'field' => $result['field'],
                    'verified' => $result['verified']
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to update verification status. Please ensure the database columns exist.'
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage() . '. Please run the migration script: tools/add_verification_columns.sql'
            ]);
        }
        exit;
    }
}
?>
