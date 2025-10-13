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
    case 'bulk_generate_sdr':
        (new LeadController())->bulkGenerateSDR();
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
    default:
        include __DIR__ . '/../views/errors/404.php';
        break;
}
