<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>
        <i class="fas fa-tag me-2"></i>
        <?= $action === 'create' ? 'Add New Status' : 'Edit Status' ?>
    </h2>
    <a href="index.php?action=status_management" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Back to Status Management
    </a>
</div>

<?php if (isset($error)): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Status Information</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="index.php?action=<?= $action === 'create' ? 'status_store' : 'status_update&id=' . $status['id'] ?>">
                    <div class="mb-3">
                        <label for="name" class="form-label">Status Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" 
                               value="<?= htmlspecialchars($status['name'] ?? '') ?>" 
                               placeholder="e.g., New Lead, Email Contact, Responded" required>
                        <div class="form-text">Enter a descriptive name for the status.</div>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="index.php?action=status_management" class="btn btn-secondary me-md-2">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>
                            <?= $action === 'create' ? 'Create Status' : 'Update Status' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
