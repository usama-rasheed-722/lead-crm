<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>
        <i class="fas fa-chart-line me-2"></i>My Quota History
    </h2>
    <div>
        <a href="index.php?action=leads_quota_sdr_view" class="btn btn-outline-primary me-2">
            <i class="fas fa-tasks me-2"></i>Current Quotas
        </a>
        <a href="index.php?action=dashboard" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
        </a>
    </div>
</div>

<!-- Date Range Filter -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-filter me-2"></i>Filter History
        </h5>
    </div>
    <div class="card-body">
        <form method="GET" action="index.php" class="row g-3">
            <input type="hidden" name="action" value="leads_quota_history">
            <div class="col-md-4">
                <label for="start_date" class="form-label">Start Date</label>
                <input type="date" class="form-control" id="start_date" name="start_date" value="<?= htmlspecialchars($startDate) ?>">
            </div>
            <div class="col-md-4">
                <label for="end_date" class="form-label">End Date</label>
                <input type="date" class="form-control" id="end_date" name="end_date" value="<?= htmlspecialchars($endDate) ?>">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-search me-2"></i>Filter
                </button>
                <a href="index.php?action=leads_quota_history" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-2"></i>Clear
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Quota History Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-history me-2"></i>
            Quota History (<?= date('M j', strtotime($startDate)) ?> - <?= date('M j, Y', strtotime($endDate)) ?>)
        </h5>
        <span class="badge bg-secondary"><?= count($quotaHistory) ?> records</span>
    </div>
    <div class="card-body p-0">
        <?php if (!empty($quotaHistory)): ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Status</th>
                            <th class="text-center">Assigned</th>
                            <th class="text-center">Completed</th>
                            <th class="text-center">Remaining</th>
                            <th class="text-center">Carry Forward</th>
                            <th class="text-center">Completion Rate</th>
                            <th class="text-center">Progress</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($quotaHistory as $quota): ?>
                            <tr>
                                <td>
                                    <span class="fw-bold"><?= date('M j, Y', strtotime($quota['assigned_date'])) ?></span>
                                    <br>
                                    <small class="text-muted"><?= date('l', strtotime($quota['assigned_date'])) ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-primary"><?= htmlspecialchars($quota['status_name']) ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="fw-bold"><?= $quota['quota_count'] ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success"><?= $quota['completed_leads'] ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-warning"><?= $quota['remaining_leads'] ?></span>
                                </td>
                                <td class="text-center">
                                    <?php if (isset($quota['quota_carry_forward']) && $quota['quota_carry_forward'] > 0): ?>
                                        <span class="badge bg-info" title="Leads carried forward from previous days">
                                            <i class="fas fa-arrow-right me-1"></i><?= $quota['quota_carry_forward'] ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php
                                    $percentage = $quota['quota_count'] > 0 ? ($quota['completed_leads'] / $quota['quota_count']) * 100 : 0;
                                    $rateClass = $percentage >= 100 ? 'text-success' : ($percentage >= 75 ? 'text-info' : ($percentage >= 50 ? 'text-warning' : 'text-danger'));
                                    ?>
                                    <span class="fw-bold <?= $rateClass ?>"><?= round($percentage, 1) ?>%</span>
                                </td>
                                <td class="text-center">
                                    <?php
                                    $progressClass = $percentage >= 100 ? 'bg-success' : ($percentage >= 75 ? 'bg-info' : ($percentage >= 50 ? 'bg-warning' : 'bg-danger'));
                                    ?>
                                    <div class="progress" style="height: 20px; width: 100px;">
                                        <div class="progress-bar <?= $progressClass ?>" role="progressbar" style="width: <?= min(100, $percentage) ?>%">
                                            <?= round($percentage, 1) ?>%
                                        </div>
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
                <h5 class="text-muted">No Quota History Found</h5>
                <p class="text-muted">No quota records found for the selected date range.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Summary Statistics -->
<?php if (!empty($quotaHistory)): ?>
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-primary"><?= count($quotaHistory) ?></h3>
                    <p class="card-text">Total Quota Days</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-info"><?= array_sum(array_column($quotaHistory, 'quota_count')) ?></h3>
                    <p class="card-text">Total Assigned</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-success"><?= array_sum(array_column($quotaHistory, 'completed_leads')) ?></h3>
                    <p class="card-text">Total Completed</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <?php
                    $totalAssigned = array_sum(array_column($quotaHistory, 'quota_count'));
                    $totalCompleted = array_sum(array_column($quotaHistory, 'completed_leads'));
                    $overallRate = $totalAssigned > 0 ? ($totalCompleted / $totalAssigned) * 100 : 0;
                    ?>
                    <h3 class="text-warning"><?= round($overallRate, 1) ?>%</h3>
                    <p class="card-text">Overall Completion Rate</p>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../layout/footer.php'; ?>
