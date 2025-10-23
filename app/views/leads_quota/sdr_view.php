<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>
        <i class="fas fa-tasks me-2"></i>My Assigned Leads Quota
    </h2>
    <div>
        <a href="index.php?action=dashboard" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
        </a>
    </div>
</div>

<!-- Quota Summary -->
<?php if (!empty($quotaSummary)): ?>
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-chart-line me-2"></i>
                Today's Quota Summary - <?= date('M j, Y', strtotime($date)) ?>
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Status</th>
                            <th class="text-center">Assigned</th>
                            <th class="text-center">Completed</th>
                            <th class="text-center">Remaining</th>
                            <th class="text-center">Progress</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($quotaSummary as $quota): ?>
                            <tr>
                                <td>
                                    <span class="badge bg-primary fs-6"><?= htmlspecialchars($quota['status_name']) ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="fw-bold fs-5"><?= $quota['quota_count'] ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success fs-6"><?= $quota['completed_leads'] ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-warning fs-6"><?= $quota['remaining_leads'] ?></span>
                                </td>
                                <td class="text-center">
                                    <?php
                                    $percentage = $quota['quota_count'] > 0 ? ($quota['completed_leads'] / $quota['quota_count']) * 100 : 0;
                                    $progressClass = $percentage >= 100 ? 'bg-success' : ($percentage >= 75 ? 'bg-info' : ($percentage >= 50 ? 'bg-warning' : 'bg-danger'));
                                    ?>
                                    <div class="progress" style="height: 20px; width: 100px;">
                                        <div class="progress-bar <?= $progressClass ?>" role="progressbar" style="width: <?= min(100, $percentage) ?>%">
                                            <?= round($percentage, 1) ?>%
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <a href="index.php?action=leads_quota_sdr_view&status_id=<?= $quota['status_id'] ?>&date=<?= $date ?>" 
                                       class="btn btn-sm <?= $quota['quota_count'] > 0 ? 'btn-primary' : 'btn-outline-secondary' ?>">
                                        <i class="fas fa-eye me-1"></i>View Leads
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="card mb-4">
        <div class="card-body text-center">
            <i class="fas fa-tasks fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">No Quotas Assigned</h5>
            <p class="text-muted">You don't have any leads quota assigned for <?= date('M j, Y', strtotime($date)) ?>.</p>
        </div>
    </div>
<?php endif; ?>

<!-- Assigned Leads Table -->
<?php if ($selectedStatus && !empty($assignedLeads)): ?>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>
                Assigned Leads - <?= htmlspecialchars($selectedStatus['name']) ?>
            </h5>
            <span class="badge bg-secondary"><?= count($assignedLeads) ?> leads</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Lead ID</th>
                            <th>Company</th>
                            <th>Contact Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Assigned At</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($assignedLeads as $lead): ?>
                            <tr class="<?= $lead['completed_at'] ? 'table-success' : '' ?>">
                                <td>
                                    <span class="fw-bold"><?= htmlspecialchars($lead['lead_id']) ?></span>
                                </td>
                                <td><?= htmlspecialchars($lead['company'] ?: 'N/A') ?></td>
                                <td><?= htmlspecialchars($lead['contact_name'] ?: 'N/A') ?></td>
                                <td><?= htmlspecialchars($lead['email'] ?: 'N/A') ?></td>
                                <td><?= htmlspecialchars($lead['phone'] ?: 'N/A') ?></td>
                                <td>
                                    <small class="text-muted">
                                        <?= date('M j, g:i A', strtotime($lead['assigned_at'])) ?>
                                    </small>
                                </td>
                                <td>
                                    <?php if ($lead['completed_at']): ?>
                                        <span class="badge bg-success">
                                            <i class="fas fa-check me-1"></i>Completed
                                        </span>
                                        <br>
                                        <small class="text-muted">
                                            <?= date('M j, g:i A', strtotime($lead['completed_at'])) ?>
                                        </small>
                                    <?php else: ?>
                                        <span class="badge bg-warning">
                                            <i class="fas fa-clock me-1"></i>Pending
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="index.php?action=lead_view&id=<?= $lead['id'] ?>" 
                                           class="btn btn-outline-primary" title="View Lead">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if (!$lead['completed_at']): ?>
                                            <button type="button" class="btn btn-outline-success mark-completed-btn" 
                                                    data-assignment-id="<?= $lead['assignment_id'] ?>" title="Mark as Completed">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-outline-warning mark-not-completed-btn" 
                                                    data-assignment-id="<?= $lead['assignment_id'] ?>" title="Mark as Not Completed">
                                                <i class="fas fa-undo"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="card-footer">
                <nav aria-label="Leads pagination">
                    <ul class="pagination pagination-sm justify-content-center mb-0">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="index.php?action=leads_quota_sdr_view&status_id=<?= $selectedStatus['id'] ?>&date=<?= $date ?>&page=<?= $page - 1 ?>">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                <a class="page-link" href="index.php?action=leads_quota_sdr_view&status_id=<?= $selectedStatus['id'] ?>&date=<?= $date ?>&page=<?= $i ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="index.php?action=leads_quota_sdr_view&status_id=<?= $selectedStatus['id'] ?>&date=<?= $date ?>&page=<?= $page + 1 ?>">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>
<?php elseif ($selectedStatus && empty($assignedLeads)): ?>
    <div class="card">
        <div class="card-body text-center">
            <i class="fas fa-list fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">No Leads Assigned</h5>
            <p class="text-muted">No leads have been assigned to you for the status "<?= htmlspecialchars($selectedStatus['name']) ?>" on <?= date('M j, Y', strtotime($date)) ?>.</p>
        </div>
    </div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle mark as completed
    document.querySelectorAll('.mark-completed-btn').forEach(button => {
        button.addEventListener('click', function() {
            const assignmentId = this.getAttribute('data-assignment-id');
            
            fetch('index.php?action=leads_quota_mark_completed', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'assignment_id=' + assignmentId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Failed to mark lead as completed');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred');
            });
        });
    });
    
    // Handle mark as not completed
    document.querySelectorAll('.mark-not-completed-btn').forEach(button => {
        button.addEventListener('click', function() {
            const assignmentId = this.getAttribute('data-assignment-id');
            
            fetch('index.php?action=leads_quota_mark_not_completed', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'assignment_id=' + assignmentId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Failed to mark lead as not completed');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred');
            });
        });
    });
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
