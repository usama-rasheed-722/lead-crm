<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-tags me-2"></i>Lead Sources Management</h2>
    <div>
        <a href="index.php?action=lead_source_create" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Add New Lead Source
        </a>
        <a href="index.php?action=dashboard" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
        </a>
    </div>
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
        <h5 class="mb-0">Lead Sources (<?= count($leadSources) ?> total)</h5>
    </div>
    <div class="card-body p-0">
        <?php if (!empty($leadSources)): ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Usage</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($leadSources as $source): ?>
                            <tr>
                                <td><?= $source['id'] ?></td>
                                <td>
                                    <span class="fw-bold"><?= htmlspecialchars($source['name']) ?></span>
                                </td>
                                <td>
                                    <?php if ($source['description']): ?>
                                        <span class="text-muted"><?= htmlspecialchars(substr($source['description'], 0, 50)) ?><?= strlen($source['description']) > 50 ? '...' : '' ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($source['is_active']): ?>
                                        <span class="badge bg-success">
                                            <i class="fas fa-check me-1"></i>Active
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">
                                            <i class="fas fa-times me-1"></i>Inactive
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?= $source['usage_count'] ?> lead(s)</span>
                                </td>
                                <td><?= date('Y-m-d H:i:s', strtotime($source['created_at'])) ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button type="button" class="btn btn-outline-<?= $source['is_active'] ? 'warning' : 'success' ?> btn-toggle-active" 
                                                data-source-id="<?= $source['id'] ?>" 
                                                data-source-name="<?= htmlspecialchars($source['name']) ?>"
                                                title="<?= $source['is_active'] ? 'Deactivate' : 'Activate' ?>">
                                            <i class="fas fa-<?= $source['is_active'] ? 'pause' : 'play' ?>"></i>
                                        </button>
                                        <a href="index.php?action=lead_source_edit&id=<?= $source['id'] ?>" 
                                           class="btn btn-outline-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if ($source['usage_count'] == 0): ?>
                                            <a href="index.php?action=lead_source_delete&id=<?= $source['id'] ?>" 
                                               class="btn btn-outline-danger btn-delete" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-outline-secondary" 
                                                    title="Cannot delete - in use by <?= $source['usage_count'] ?> lead(s)" disabled>
                                                <i class="fas fa-lock"></i>
                                            </button>
                                        <?php endif; ?>
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
                <h5 class="text-muted">No Lead Sources Found</h5>
                <p class="text-muted">Get started by creating your first lead source.</p>
                <a href="index.php?action=lead_source_create" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Add New Lead Source
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
            if (!confirm('Are you sure you want to delete this lead source? This action cannot be undone.')) {
                e.preventDefault();
            }
        });
    });
    
    // Toggle active status
    document.querySelectorAll('.btn-toggle-active').forEach(btn => {
        btn.addEventListener('click', function() {
            const sourceId = this.dataset.sourceId;
            const sourceName = this.dataset.sourceName;
            const action = this.title.toLowerCase();
            
            if (confirm(`Are you sure you want to ${action} "${sourceName}"?`)) {
                fetch(`index.php?action=lead_source_toggle_active&id=${sourceId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Reload the page to show updated status
                            location.reload();
                        } else {
                            alert('Error: ' + (data.error || 'Failed to update lead source status'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error: Failed to update lead source status');
                    });
            }
        });
    });
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
