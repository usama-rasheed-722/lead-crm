<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-tachometer-alt me-2"></i>Dashboard</h2>
    <form class="d-flex align-items-center" method="GET" action="index.php">
        <input type="hidden" name="action" value="dashboard">
        <?php if (in_array((auth_user()['role'] ?? ''), ['admin','manager'])): ?>
        <div class="me-2">
            <select class="form-select" name="sdr_id">
                <option value="">All SDRs</option>
                <?php foreach (($users ?? []) as $u): ?>
                    <option value="<?= $u['sdr_id'] ?>" <?= (($selected_sdr_id ?? '') == ($u['sdr_id'])) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($u['full_name'] ?: $u['username']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>
        <div class="me-2">
            <input type="date" class="form-control" name="date_from" value="<?= htmlspecialchars($date_from ?? '') ?>" placeholder="From">
        </div>
        <div class="me-2">
            <input type="date" class="form-control" name="date_to" value="<?= htmlspecialchars($date_to ?? '') ?>" placeholder="To">
        </div>
        <button type="submit" class="btn btn-outline-primary me-2">Apply</button>
        <a href="index.php?action=dashboard" class="btn btn-outline-secondary">Clear</a>
    </form>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-2">
        <div class="card summary-card">
            <div class="card-body">
                <div class="number text-primary"><?= $summary['total'] ?? 0 ?></div>
                <div class="label">Total Leads</div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card summary-card">
            <div class="card-body">
                <div class="number text-success"><?= $summary['unique'] ?? 0 ?></div>
                <div class="label">Unique Leads</div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card summary-card">
            <div class="card-body">
                <div class="number text-warning"><?= $summary['duplicate'] ?? 0 ?></div>
                <div class="label">Duplicates</div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card summary-card">
            <div class="card-body">
                <div class="number text-danger"><?= $summary['incomplete'] ?? 0 ?></div>
                <div class="label">Incomplete</div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card summary-card">
            <div class="card-body">
                <div class="number text-info"><?= $summary['linkedin'] ?? 0 ?></div>
                <div class="label">LinkedIn</div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card summary-card">
            <div class="card-body">
                <div class="number text-info"><?= $summary['clutch'] ?? 0 ?></div>
                <div class="label">Clutch</div>
            </div>
        </div>
    </div>
    <div class="col-md-2 mt-3">
        <div class="card summary-card">
            <div class="card-body">
                <div class="number text-info"><?= $summary['gmb'] ?? 0 ?></div>
                <div class="label">GMB</div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Leads -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-users me-2"></i>Recent Leads</h5>
                <a href="index.php?action=leads" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                <?php if (!empty($recentLeads)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Lead ID</th>
                                    <th>Name</th>
                                    <th>Company</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentLeads as $lead): ?>
                                    <tr>
                                        <td>
                                            <a href="index.php?action=lead_view&id=<?= $lead['id'] ?>" class="text-decoration-none">
                                                <?= htmlspecialchars($lead['lead_id']) ?>
                                            </a>
                                        </td>
                                        <td><?= htmlspecialchars($lead['name'] ?: 'N/A') ?></td>
                                        <td><?= htmlspecialchars($lead['company'] ?: 'N/A') ?></td>
                                        <td><?= htmlspecialchars($lead['email'] ?: 'N/A') ?></td>
                                        <td>
                                            <?php
                                            $statusClass = 'status-incomplete';
                                            if ($lead['duplicate_status'] === 'unique') { $statusClass = 'status-unique'; }
                                            elseif ($lead['duplicate_status'] === 'duplicate') { $statusClass = 'status-duplicate'; }
                                            $statusIcon = '⚠️';
                                            if ($lead['duplicate_status'] === 'unique') { $statusIcon = '✅'; }
                                            elseif ($lead['duplicate_status'] === 'duplicate') { $statusIcon = '🔁'; }
                                            ?>
                                            <span class="status-badge <?= $statusClass ?>">
                                                <?= $statusIcon ?> <?= ucfirst($lead['duplicate_status']) ?>
                                            </span>
                                        </td>
                                        <td><?= date('M j, Y', strtotime($lead['created_at'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No leads found. Start by adding your first lead!</p>
                        <a href="index.php?action=lead_add" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Add First Lead
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Quick Actions & Recent Activity -->
    <div class="col-md-4">
        <!-- Quick Actions -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="index.php?action=lead_add" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Add New Lead
                    </a>
                    <a href="index.php?action=leads" class="btn btn-outline-primary">
                        <i class="fas fa-list me-2"></i>View All Leads
                    </a>
                    <a href="index.php?action=import" class="btn btn-outline-success">
                        <i class="fas fa-file-import me-2"></i>Import Leads
                    </a>
                    <?php if (auth_user()['role'] === 'admin'): ?>
                        <a href="index.php?action=users" class="btn btn-outline-secondary">
                            <i class="fas fa-users me-2"></i>Manage Users
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <?php if (!empty($recentActivity)): ?>
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Activity</h5>
            </div>
            <div class="card-body">
                <?php foreach ($recentActivity as $activity): ?>
                    <div class="d-flex mb-3">
                        <div class="flex-shrink-0">
                            <?php
                            $icon = 'sticky-note';
                            if ($activity['type'] === 'call') { $icon = 'phone'; }
                            elseif ($activity['type'] === 'email') { $icon = 'envelope'; }
                            elseif ($activity['type'] === 'update') { $icon = 'edit'; }
                            ?>
                            <i class="fas fa-<?= $icon ?> text-primary"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="small text-muted">
                                <?= htmlspecialchars($activity['full_name'] ?: $activity['username']) ?>
                                • <?= date('M j, g:i A', strtotime($activity['created_at'])) ?>
                            </div>
                            <div class="small">
                                <a href="index.php?action=lead_view&id=<?= $activity['lead_id'] ?>" class="text-decoration-none">
                                    <?= htmlspecialchars($activity['lead_name'] ?: $activity['lead_id']) ?>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>