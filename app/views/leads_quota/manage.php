<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>
        <i class="fas fa-list me-2"></i>Manage Leads Quotas
    </h2>
    <div>
        <a href="index.php?action=leads_quota_assign" class="btn btn-primary me-2">
            <i class="fas fa-plus me-2"></i>Assign New Quota
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

<!-- Date Filter -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <form method="GET" action="index.php" class="d-flex gap-2">
                    <input type="hidden" name="action" value="leads_quota_manage">
                    <div class="flex-grow-1">
                        <label for="date" class="form-label">Filter by Date</label>
                        <input type="date" class="form-control" id="date" name="date" value="<?= htmlspecialchars($date) ?>">
                    </div>
                    <div class="d-flex align-items-end">
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="fas fa-filter me-2"></i>Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Quotas Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Quotas for <?= date('M j, Y', strtotime($date)) ?></h5>
        <span class="badge bg-secondary"><?= count($quotas) ?> quotas</span>
    </div>
    <div class="card-body p-0">
        <?php if (!empty($quotas)): ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>SDR</th>
                            <th>Status</th>
                            <th>Quota Count</th>
                            <th>Assigned Leads</th>
                            <th>Completed Leads</th>
                            <th>Remaining</th>
                            <th>Progress</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($quotas as $quota): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-user-circle me-2 text-muted"></i>
                                        <span><?= htmlspecialchars($quota['user_name']) ?></span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-primary"><?= htmlspecialchars($quota['status_name']) ?></span>
                                </td>
                                <td>
                                    <span class="fw-bold"><?= $quota['quota_count'] ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?= $quota['assigned_leads'] ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-success"><?= $quota['completed_leads'] ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-warning"><?= $quota['quota_count'] - $quota['completed_leads'] ?></span>
                                </td>
                                <td>
                                    <?php
                                    $percentage = $quota['quota_count'] > 0 ? ($quota['completed_leads'] / $quota['quota_count']) * 100 : 0;
                                    $progressClass = $percentage >= 100 ? 'bg-success' : ($percentage >= 75 ? 'bg-info' : ($percentage >= 50 ? 'bg-warning' : 'bg-danger'));
                                    ?>
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar <?= $progressClass ?>" role="progressbar" style="width: <?= min(100, $percentage) ?>%">
                                            <?= round($percentage, 1) ?>%
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="index.php?action=leads_quota_view&user_id=<?= $quota['user_id'] ?>&status_id=<?= $quota['status_id'] ?>&date=<?= $date ?>" 
                                           class="btn btn-outline-primary" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="index.php?action=leads_quota_delete&id=<?= $quota['id'] ?>" 
                                           class="btn btn-outline-danger" title="Delete Quota"
                                           onclick="return confirm('Are you sure you want to delete this quota?')">
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
                <i class="fas fa-tasks fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Quotas Found</h5>
                <p class="text-muted">No quotas have been assigned for <?= date('M j, Y', strtotime($date)) ?>.</p>
                <a href="index.php?action=leads_quota_assign" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Assign First Quota
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Summary Statistics -->
<?php if (!empty($quotas)): ?>
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-primary"><?= count($quotas) ?></h3>
                    <p class="card-text">Total Quotas</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-info"><?= array_sum(array_column($quotas, 'quota_count')) ?></h3>
                    <p class="card-text">Total Leads Assigned</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-success"><?= array_sum(array_column($quotas, 'completed_leads')) ?></h3>
                    <p class="card-text">Total Completed</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-warning"><?= array_sum(array_column($quotas, 'quota_count')) - array_sum(array_column($quotas, 'completed_leads')) ?></h3>
                    <p class="card-text">Total Remaining</p>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../layout/footer.php'; ?>
