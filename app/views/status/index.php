<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-tags me-2"></i>Status Management</h2>
    <a href="index.php?action=status_add" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Add New Status
    </a>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle me-2"></i>
        <?= htmlspecialchars($_GET['success']) ?>
    </div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <?= htmlspecialchars($_GET['error']) ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Available Statuses</h5>
    </div>
    <div class="card-body p-0">
        <?php if (!empty($statuses)): ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($statuses as $status): ?>
                            <tr>
                                <td><?= $status['id'] ?></td>
                                <td>
                                    <span class="badge bg-secondary">
                                        <?= htmlspecialchars($status['name']) ?>
                                    </span>
                                </td>
                                <td><?= date('Y-m-d H:i:s', strtotime($status['created_at'])) ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="index.php?action=status_edit&id=<?= $status['id'] ?>" 
                                           class="btn btn-outline-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="index.php?action=status_delete&id=<?= $status['id'] ?>" 
                                           class="btn btn-outline-danger btn-delete" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-tags fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No statuses found</h5>
                <p class="text-muted">Create your first status to get started.</p>
                <a href="index.php?action=status_add" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Add New Status
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Delete confirmation
    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to delete this status? This action cannot be undone.')) {
                e.preventDefault();
            }
        });
    });
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
