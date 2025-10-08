<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="text-center py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <i class="fas fa-exclamation-triangle fa-5x text-warning mb-4"></i>
                <h1 class="display-4">404</h1>
                <h2 class="mb-4">Page Not Found</h2>
                <p class="lead text-muted mb-4">
                    The page you're looking for doesn't exist or has been moved.
                </p>
                <a href="index.php?action=dashboard" class="btn btn-primary">
                    <i class="fas fa-home me-2"></i>Go to Dashboard
                </a>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
