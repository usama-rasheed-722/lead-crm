<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>
        <i class="fas fa-chart-line me-2"></i>Quota Management
    </h2>
    <a href="index.php?action=users" class="btn btn-outline-secondary">
        <i class="fas fa-users me-2"></i>Back to Users
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
        <h5 class="mb-0">All User Quotas</h5>
    </div>
    <div class="card-body p-0">
        <?php if (!empty($quotas)): ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Status</th>
                            <th>Quota Limit</th>
                            <th>Days Limit</th>
                            <th>Usage</th>
                            <th>Remaining</th>
                            <th>Progress</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($quotas as $quota): ?>
                            <tr>
                                <td>
                                    <div>
                                        <strong><?= htmlspecialchars($quota['user_full_name'] ?: $quota['user_name']) ?></strong>
                                        <br>
                                        <small class="text-muted"><?= htmlspecialchars($quota['user_name']) ?></small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">
                                        <?= htmlspecialchars($quota['status_name']) ?>
                                    </span>
                                </td>
                                <td><?= $quota['quota_limit'] ?></td>
                                <td><?= $quota['days_limit'] ?> days</td>
                                <td>
                                    <span class="badge <?= $quota['usage_count'] >= $quota['quota_limit'] ? 'bg-danger' : ($quota['usage_count'] >= ($quota['quota_limit'] * 0.9) ? 'bg-warning' : 'bg-success') ?>">
                                        <?= $quota['usage_count'] ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-info">
                                        <?= $quota['remaining'] ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="progress" style="width: 100px;">
                                        <div class="progress-bar <?= $quota['usage_percentage'] >= 100 ? 'bg-danger' : ($quota['usage_percentage'] >= 90 ? 'bg-warning' : 'bg-success') ?>" 
                                             style="width: <?= min(100, $quota['usage_percentage']) ?>%">
                                            <?= $quota['usage_percentage'] ?>%
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button type="button" class="btn btn-outline-primary btn-edit-quota" 
                                                data-quota-id="<?= $quota['id'] ?>"
                                                data-quota-limit="<?= $quota['quota_limit'] ?>"
                                                data-days-limit="<?= $quota['days_limit'] ?>"
                                                title="Edit Quota">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <a href="index.php?action=quota_delete&id=<?= $quota['id'] ?>" 
                                           class="btn btn-outline-danger btn-delete-quota" 
                                           title="Delete Quota">
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
                <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Quotas Found</h5>
                <p class="text-muted">No user quotas have been assigned yet.</p>
                <a href="index.php?action=users" class="btn btn-primary">
                    <i class="fas fa-users me-2"></i>Manage Users
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Edit Quota Modal -->
<div class="modal fade" id="editQuotaModal" tabindex="-1" aria-labelledby="editQuotaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editQuotaModalLabel">Edit Quota</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editQuotaForm" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_quota_limit" class="form-label">Quota Limit</label>
                        <input type="number" class="form-control" id="edit_quota_limit" name="quota_limit" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_days_limit" class="form-label">Days Limit</label>
                        <input type="number" class="form-control" id="edit_days_limit" name="days_limit" min="1" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Quota</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const editQuotaModal = new bootstrap.Modal(document.getElementById('editQuotaModal'));
    const editQuotaForm = document.getElementById('editQuotaForm');
    
    // Edit quota functionality
    document.querySelectorAll('.btn-edit-quota').forEach(btn => {
        btn.addEventListener('click', function() {
            const quotaId = this.dataset.quotaId;
            const quotaLimit = this.dataset.quotaLimit;
            const daysLimit = this.dataset.daysLimit;
            
            document.getElementById('edit_quota_limit').value = quotaLimit;
            document.getElementById('edit_days_limit').value = daysLimit;
            editQuotaForm.action = `index.php?action=quota_update&id=${quotaId}`;
            editQuotaModal.show();
        });
    });
    
    // Delete confirmation
    document.querySelectorAll('.btn-delete-quota').forEach(btn => {
        btn.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to delete this quota? This action cannot be undone.')) {
                e.preventDefault();
            }
        });
    });
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
