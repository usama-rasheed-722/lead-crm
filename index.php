<?php
session_start();

// Load core files
require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/Controller.php';
require_once __DIR__ . '/core/Model.php';

// Load helpers
require_once __DIR__ . '/app/helpers.php';

// Load all models
foreach (glob(__DIR__ . '/app/models/*.php') as $file) {
    require_once $file;
}

// Load all controllers
foreach (glob(__DIR__ . '/app/controllers/*.php') as $file) {
    require_once $file;
}

// Define route action
$action = $_GET['action'] ?? 'dashboard';

// Auth check for non-login pages
$publicRoutes = ['login', 'logout'];
if (!in_array($action, $publicRoutes) && empty($_SESSION['user'])) {
    header('Location: index.php?action=login');
    exit;
}

// Route handling
switch ($action) {
    case 'login':
        (new AuthController())->login();
        break;
    case 'logout':
        (new AuthController())->logout();
        break;
    case 'dashboard':
        (new DashboardController())->index();
        break;
    case 'leads':
        (new LeadController())->index();
        break;
    case 'lead_view':
        (new LeadController())->viewLead($_GET['id'] ?? null);
        break;
    case 'lead_add':
        (new LeadController())->create();
        break;
    case 'lead_store':
        (new LeadController())->store();
        break;
    case 'lead_edit':
        (new LeadController())->edit($_GET['id'] ?? null);
        break;
    case 'lead_update':
        (new LeadController())->update($_GET['id'] ?? null);
        break;
    case 'lead_delete':
        (new LeadController())->delete($_GET['id'] ?? null);
        break;
    case 'generate_sdr':
        (new LeadController())->generateSDR($_GET['id'] ?? null);
        break;
    
    case 'bulk_delete':
        (new LeadController())->bulkDelete();
        break;
    case 'find_duplicates':
        (new LeadController())->findDuplicates($_GET['id'] ?? null);
        break;
    case 'merge_duplicates':
        (new LeadController())->mergeDuplicates($_GET['id'] ?? null);
        break;
    case 'leads_management':
        (new LeadController())->leadsManagement();
        break;
    case 'bulk_update_status':
        (new LeadController())->bulkUpdateStatus();
        break;
    case 'bulk_update_status_with_custom_fields':
        (new LeadController())->bulkUpdateStatusWithCustomFields();
        break;
    case 'update_status_with_custom_fields':
        (new LeadController())->updateStatusWithCustomFields();
        break;
    case 'get_custom_fields_for_status':
        (new LeadController())->getCustomFieldsForStatus();
        break;
    case 'lead_status_history':
        (new LeadController())->statusHistory();
        break;
    case 'status_management':
        (new StatusController())->index();
        break;
    case 'status_add':
        (new StatusController())->create();
        break;
    case 'status_store':
        (new StatusController())->store();
        break;
    case 'status_edit':
        (new StatusController())->edit($_GET['id'] ?? null);
        break;
    case 'status_update':
        (new StatusController())->update($_GET['id'] ?? null);
        break;
    case 'status_delete':
        (new StatusController())->delete($_GET['id'] ?? null);
        break;
    case 'get_statuses':
        (new StatusController())->getStatuses();
        break;
    case 'create_custom_field':
        (new StatusController())->createCustomField();
        break;
    case 'update_custom_field':
        (new StatusController())->updateCustomField($_GET['id'] ?? null);
        break;
    case 'delete_custom_field':
        (new StatusController())->deleteCustomField($_GET['id'] ?? null);
        break;
    case 'set_status_as_default':
        (new StatusController())->setAsDefault($_GET['id'] ?? null);
        break;
    case 'update_status_sequence':
        (new StatusController())->updateSequence($_GET['id'] ?? null);
        break;
    case 'update_status_sequences':
        (new StatusController())->updateSequences();
        break;
    case 'lead_sources':
        (new LeadSourceController())->index();
        break;
    case 'lead_source_create':
        (new LeadSourceController())->create();
        break;
    case 'lead_source_store':
        (new LeadSourceController())->store();
        break;
    case 'lead_source_edit':
        (new LeadSourceController())->edit($_GET['id'] ?? null);
        break;
    case 'lead_source_update':
        (new LeadSourceController())->update($_GET['id'] ?? null);
        break;
    case 'lead_source_delete':
        (new LeadSourceController())->delete($_GET['id'] ?? null);
        break;
    case 'lead_source_toggle_active':
        (new LeadSourceController())->toggleActive($_GET['id'] ?? null);
        break;
    case 'get_lead_sources':
        (new LeadSourceController())->getLeadSources();
        break;
    case 'import':
        (new ImportController())->index();
        break;
    case 'import_upload':
        (new ImportController())->upload();
        break;
    case 'export_csv':
        (new ImportController())->exportCsv();
        break;
    case 'export_excel':
        (new ImportController())->exportExcel();
        break;
    case 'notes_add':
        (new NoteController())->add();
        break;
    case 'notes_delete':
        (new NoteController())->delete($_GET['id'] ?? null);
        break;
    case 'users':
        (new UserController())->index();
        break;
    case 'user_add':
        (new UserController())->create();
        break;
    case 'user_store':
        (new UserController())->store();
        break;
    case 'user_edit':
        (new UserController())->edit($_GET['id'] ?? null);
        break;
    case 'user_update':
        (new UserController())->update($_GET['id'] ?? null);
        break;
    case 'user_delete':
        (new UserController())->delete($_GET['id'] ?? null);
        break;
    case 'quota_management':
        (new QuotaController())->index();
        break;
    case 'manage_user_quotas':
        (new QuotaController())->manageUserQuotas($_GET['id'] ?? null);
        break;
    case 'quota_store':
        (new QuotaController())->store();
        break;
    case 'quota_update':
        (new QuotaController())->update($_GET['id'] ?? null);
        break;
    case 'quota_delete':
        (new QuotaController())->delete($_GET['id'] ?? null);
        break;
    case 'get_quota_usage':
        (new QuotaController())->getQuotaUsage();
        break;
    case 'get_quota_status':
        (new QuotaController())->getQuotaStatus();
        break;
    case 'get_user_quotas':
        (new QuotaController())->getUserQuotas();
        break;
    case 'get_quota_summary':
        (new QuotaController())->getQuotaSummary();
        break;
    
    // Leads Quota Management
    case 'leads_quota_assign':
        (new LeadsQuotaController())->assign();
        break;
    case 'leads_quota_store':
        (new LeadsQuotaController())->store();
        break;
    case 'leads_quota_manage':
        (new LeadsQuotaController())->manage();
        break;
    case 'leads_quota_sdr_view':
        (new LeadsQuotaController())->sdrView();
        break;
    case 'leads_quota_mark_completed':
        (new LeadsQuotaController())->markCompleted();
        break;
    case 'leads_quota_mark_not_completed':
        (new LeadsQuotaController())->markNotCompleted();
        break;
    case 'leads_quota_delete':
        (new LeadsQuotaController())->delete();
        break;
    default:
        include __DIR__ . '/../views/errors/404.php';
        break;
}
