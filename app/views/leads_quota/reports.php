<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>
        <i class="fas fa-chart-bar me-2"></i>Quota Reports & Analytics
    </h2>
    <div>
        <a href="index.php?action=leads_quota_manage" class="btn btn-outline-primary me-2">
            <i class="fas fa-list me-2"></i>Manage Quotas
        </a>
        <a href="index.php?action=dashboard" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
        </a>
    </div>
</div>

<!-- Report Filters -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-filter me-2"></i>Report Filters
        </h5>
    </div>
    <div class="card-body">
        <form method="GET" action="index.php" class="row g-3">
            <input type="hidden" name="action" value="leads_quota_reports">
            <div class="col-md-3">
                <label for="start_date" class="form-label">Start Date</label>
                <input type="date" class="form-control" id="start_date" name="start_date" value="<?= htmlspecialchars($startDate) ?>">
            </div>
            <div class="col-md-3">
                <label for="end_date" class="form-label">End Date</label>
                <input type="date" class="form-control" id="end_date" name="end_date" value="<?= htmlspecialchars($endDate) ?>">
            </div>
            <div class="col-md-3">
                <label for="user_id" class="form-label">SDR</label>
                <select class="form-select" id="user_id" name="user_id">
                    <option value="">All SDRs</option>
                    <?php foreach ($users as $user): ?>
                        <?php if ($user['role'] === 'sdr'): ?>
                            <option value="<?= $user['id'] ?>" <?= ($selectedUserId ?? '') == $user['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($user['full_name'] ?: $user['username']) ?>
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-search me-2"></i>Generate Report
                </button>
                <a href="index.php?action=leads_quota_reports" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-2"></i>Clear
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Summary Statistics -->
<?php if (!empty($quotaStats)): ?>
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-primary"><?= $quotaStats['total_quotas'] ?></h3>
                    <p class="card-text">Total Quotas</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-info"><?= $quotaStats['total_users'] ?></h3>
                    <p class="card-text">Active SDRs</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-secondary"><?= $quotaStats['total_assigned'] ?></h3>
                    <p class="card-text">Total Assigned</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-success"><?= $quotaStats['total_completed'] ?></h3>
                    <p class="card-text">Total Completed</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-warning"><?= $quotaStats['total_remaining'] ?></h3>
                    <p class="card-text">Total Remaining</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-primary"><?= round($quotaStats['avg_completion_rate'], 1) ?>%</h3>
                    <p class="card-text">Avg Completion Rate</p>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Detailed Report Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-table me-2"></i>
            Detailed Quota Report (<?= date('M j', strtotime($startDate)) ?> - <?= date('M j, Y', strtotime($endDate)) ?>)
        </h5>
        <div>
            <span class="badge bg-secondary me-2"><?= count($quotaReport) ?> records</span>
            <button type="button" class="btn btn-sm btn-outline-success" id="exportBtn">
                <i class="fas fa-download me-1"></i>Export CSV
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <?php if (!empty($quotaReport)): ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="quotaReportTable">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>SDR</th>
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
                        <?php foreach ($quotaReport as $quota): ?>
                            <tr>
                                <td>
                                    <span class="fw-bold"><?= date('M j, Y', strtotime($quota['assigned_date'])) ?></span>
                                    <br>
                                    <small class="text-muted"><?= date('l', strtotime($quota['assigned_date'])) ?></small>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-user-circle me-2 text-muted"></i>
                                        <span><?= htmlspecialchars($quota['user_name']) ?></span>
                                    </div>
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
                                    $percentage = $quota['completion_percentage'];
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
                <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Report Data Found</h5>
                <p class="text-muted">No quota data found for the selected criteria.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Performance Analysis -->
<?php if (!empty($quotaReport)): ?>
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-trophy me-2"></i>Top Performers
                    </h5>
                </div>
                <div class="card-body">
                    <?php
                    // Group by user and calculate average completion rate
                    $userStats = [];
                    foreach ($quotaReport as $quota) {
                        $userId = $quota['user_id'];
                        if (!isset($userStats[$userId])) {
                            $userStats[$userId] = [
                                'name' => $quota['user_name'],
                                'total_assigned' => 0,
                                'total_completed' => 0,
                                'count' => 0
                            ];
                        }
                        $userStats[$userId]['total_assigned'] += $quota['quota_count'];
                        $userStats[$userId]['total_completed'] += $quota['completed_leads'];
                        $userStats[$userId]['count']++;
                    }
                    
                    // Calculate completion rates and sort
                    foreach ($userStats as &$stats) {
                        $stats['completion_rate'] = $stats['total_assigned'] > 0 ? 
                            ($stats['total_completed'] / $stats['total_assigned']) * 100 : 0;
                    }
                    uasort($userStats, function($a, $b) {
                        return $b['completion_rate'] <=> $a['completion_rate'];
                    });
                    
                    $topPerformers = array_slice($userStats, 0, 5, true);
                    ?>
                    <?php if (!empty($topPerformers)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($topPerformers as $userId => $stats): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="fw-bold"><?= htmlspecialchars($stats['name']) ?></span>
                                        <br>
                                        <small class="text-muted"><?= $stats['count'] ?> quota days</small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-success fs-6"><?= round($stats['completion_rate'], 1) ?>%</span>
                                        <br>
                                        <small class="text-muted"><?= $stats['total_completed'] ?>/<?= $stats['total_assigned'] ?></small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>Areas for Improvement
                    </h5>
                </div>
                <div class="card-body">
                    <?php
                    // Find quotas with low completion rates
                    $lowPerformance = array_filter($quotaReport, function($quota) {
                        return $quota['completion_percentage'] < 50;
                    });
                    ?>
                    <?php if (!empty($lowPerformance)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach (array_slice($lowPerformance, 0, 5) as $quota): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="fw-bold"><?= htmlspecialchars($quota['user_name']) ?></span>
                                        <br>
                                        <small class="text-muted"><?= htmlspecialchars($quota['status_name']) ?> - <?= date('M j', strtotime($quota['assigned_date'])) ?></small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-danger fs-6"><?= round($quota['completion_percentage'], 1) ?>%</span>
                                        <br>
                                        <small class="text-muted"><?= $quota['completed_leads'] ?>/<?= $quota['quota_count'] ?></small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-3">
                            <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                            <p class="text-muted mb-0">All quotas are performing well!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const exportBtn = document.getElementById('exportBtn');
    
    if (exportBtn) {
        exportBtn.addEventListener('click', function() {
            // Get current filter parameters
            const urlParams = new URLSearchParams(window.location.search);
            const exportUrl = 'index.php?action=export_quota_report&' + urlParams.toString();
            
            // Create a temporary link to trigger download
            const link = document.createElement('a');
            link.href = exportUrl;
            link.download = 'quota_report_' + new Date().toISOString().split('T')[0] + '.csv';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });
    }
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
