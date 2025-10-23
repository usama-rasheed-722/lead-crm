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
                            <th>Sequence</th>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($statuses as $status): ?>
                            <tr>
                                <td><?= $status['id'] ?></td>
                                <td>
                                    <div class="input-group input-group-sm" style="width: 80px;">
                                        <input type="number" 
                                               class="form-control sequence-input" 
                                               value="<?= $status['sequence'] ?>" 
                                               data-status-id="<?= $status['id'] ?>"
                                               min="0"
                                               style="text-align: center;">
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">
                                        <?= htmlspecialchars($status['name']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap gap-1">
                                        <?php if ($status['is_default']): ?>
                                            <span class="badge bg-success">
                                                <i class="fas fa-star me-1"></i>Default
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($status['restrict_bulk_update']): ?>
                                            <span class="badge bg-warning">
                                                <i class="fas fa-ban me-1"></i>No Bulk
                                            </span>
                                        <?php endif; ?>
                                        <?php if (!$status['is_default'] && !$status['restrict_bulk_update']): ?>
                                            <span class="badge bg-light text-dark">Standard</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td><?= date('Y-m-d H:i:s', strtotime($status['created_at'])) ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <?php if (!$status['is_default']): ?>
                                            <button type="button" class="btn btn-outline-success btn-set-default" 
                                                    data-status-id="<?= $status['id'] ?>" 
                                                    data-status-name="<?= htmlspecialchars($status['name']) ?>"
                                                    title="Set as Default">
                                                <i class="fas fa-star"></i>
                                            </button>
                                        <?php endif; ?>
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
    
    // Set as default functionality
    document.querySelectorAll('.btn-set-default').forEach(btn => {
        btn.addEventListener('click', function() {
            const statusId = this.dataset.statusId;
            const statusName = this.dataset.statusName;
            
            if (confirm(`Are you sure you want to set "${statusName}" as the default status? This will unset any current default status.`)) {
                fetch(`index.php?action=set_status_as_default&id=${statusId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Reload the page to show updated status
                            location.reload();
                        } else {
                            alert('Error: ' + (data.error || 'Failed to set status as default'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error: Failed to set status as default');
                    });
            }
        });
    });

    // Sequence update functionality
    document.querySelectorAll('.sequence-input').forEach(input => {
        let timeoutId;
        
        input.addEventListener('input', function() {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => {
                const statusId = this.dataset.statusId;
                const sequence = this.value;
                
                if (sequence !== this.defaultValue) {
                    // Create form data
                    const formData = new FormData();
                    formData.append('sequence', sequence);
                    
                    fetch(`index.php?action=update_status_sequence&id=${statusId}`, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => {
                        if (response.ok) {
                            this.defaultValue = sequence;
                            // Show success indicator
                            this.style.borderColor = '#28a745';
                            setTimeout(() => {
                                this.style.borderColor = '';
                            }, 1000);
                        } else {
                            // Show error indicator
                            this.style.borderColor = '#dc3545';
                            setTimeout(() => {
                                this.style.borderColor = '';
                            }, 2000);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        this.style.borderColor = '#dc3545';
                        setTimeout(() => {
                            this.style.borderColor = '';
                        }, 2000);
                    });
                }
            }, 500); // 500ms delay to avoid too many requests
        });
    });
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
