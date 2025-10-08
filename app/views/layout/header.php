<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'CRM Lead Manager' ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?= base_url('public/assets/css/app.css') ?>" rel="stylesheet">
</head>
<body>
    <?php if (auth_user()): ?>
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= base_url('index.php?action=dashboard') ?>">
                <i class="fas fa-chart-line me-2"></i>CRM Lead Manager
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i>
                            <?= htmlspecialchars(auth_user()['full_name'] ?? auth_user()['username']) ?>
                            <span class="badge bg-secondary ms-1"><?= ucfirst(auth_user()['role']) ?></span>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?= base_url('index.php?action=logout') ?>">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link <?= ($_GET['action'] ?? '') === 'dashboard' ? 'active' : '' ?>" 
                               href="<?= base_url('index.php?action=dashboard') ?>">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= ($_GET['action'] ?? '') === 'leads' ? 'active' : '' ?>" 
                               href="<?= base_url('index.php?action=leads') ?>">
                                <i class="fas fa-users me-2"></i>Leads
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= ($_GET['action'] ?? '') === 'import' ? 'active' : '' ?>" 
                               href="<?= base_url('index.php?action=import') ?>">
                                <i class="fas fa-file-import me-2"></i>Import/Export
                            </a>
                        </li>
                        <?php if (auth_user()['role'] === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= ($_GET['action'] ?? '') === 'users' ? 'active' : '' ?>" 
                               href="<?= base_url('index.php?action=users') ?>">
                                <i class="fas fa-user-cog me-2"></i>Users
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
    <?php endif; ?>