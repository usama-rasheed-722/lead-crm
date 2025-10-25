<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>
        <i class="fas fa-tasks me-2"></i>My Assigned Leads Quota
    </h2>
    <div>
        <a href="index.php?action=leads_quota_history" class="btn btn-outline-info me-2">
            <i class="fas fa-chart-line me-2"></i>Quota History
        </a>
        <a href="index.php?action=dashboard" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
        </a>
    </div>
</div>

<!-- Date and Status Filters -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-filter me-2"></i>Filters & Navigation
        </h5>
    </div>
    <div class="card-body">
        <form method="GET" action="index.php" class="row g-3">
            <input type="hidden" name="action" value="leads_quota_sdr_view">
            <div class="col-md-3">
                <label for="date" class="form-label">Date</label>
                <input type="date" class="form-control" id="date" name="date" value="<?= htmlspecialchars($date) ?>">
            </div>
            <div class="col-md-3">
                <label for="status_id" class="form-label">Status</label>
                <select class="form-select" id="status_id" name="status_id">
                    <option value="">All Statuses</option>
                    <?php foreach ($quotaSummary as $quota): ?>
                        <option value="<?= $quota['status_id'] ?>" <?= ($statusId ?? '') == $quota['status_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($quota['status_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-search me-2"></i>Filter
                </button>
                <a href="index.php?action=leads_quota_sdr_view" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-2"></i>Clear
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Quota Summary -->
<?php if (!empty($quotaSummary)): ?>
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-chart-line me-2"></i>
                Quota Summary - <?= date('M j, Y', strtotime($date)) ?>
            </h5>
            <div>
                <span class="badge bg-info me-2">
                    <i class="fas fa-sync-alt me-1"></i>Auto-Rollover Enabled
                </span>
                <button type="button" class="btn btn-sm btn-outline-primary" id="refreshQuotaBtn">
                    <i class="fas fa-sync-alt me-1"></i>Refresh
                </button>
            </div>
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
                            <th class="text-center">Carry Forward</th>
                            <th class="text-center">Progress</th>
                            <th class="text-center">Instructions</th>
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
                                    <?php if (isset($quota['quota_carry_forward']) && $quota['quota_carry_forward'] > 0): ?>
                                        <span class="badge bg-info fs-6" title="Leads carried forward from previous days">
                                            <i class="fas fa-arrow-right me-1"></i><?= $quota['quota_carry_forward'] ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php
                                    $percentage = $quota['quota_count'] > 0 ? ($quota['completed_leads'] / $quota['quota_count']) * 100 : 0;
                                    $progressClass = $percentage >= 100 ? 'bg-success' : ($percentage >= 75 ? 'bg-info' : ($percentage >= 50 ? 'bg-warning' : 'bg-danger'));
                                    ?>
                                    <div class="progress" style="height: 20px; width: 120px;">
                                        <div class="progress-bar <?= $progressClass ?>" role="progressbar" style="width: <?= min(100, $percentage) ?>%">
                                            <?= round($percentage, 1) ?>%
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <?php if (!empty($quota['explanation'])): ?>
                                        <button type="button" class="btn btn-sm btn-outline-info" 
                                                data-bs-toggle="tooltip" 
                                                data-bs-placement="top" 
                                                title="<?= htmlspecialchars($quota['explanation']) ?>">
                                            <i class="fas fa-info-circle me-1"></i>View
                                        </button>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
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
            <div>
                <span class="badge bg-secondary me-2"><?= count($assignedLeads) ?> leads</span>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="columnsBtn">
                    <i class="fas fa-columns me-1"></i>Columns
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th data-col-key="lead_id">Lead ID</th>
                            <th data-col-key="company">Company</th>
                            <th data-col-key="contact_name">Contact Name</th>
                            <th data-col-key="email">Email</th>
                            <th data-col-key="phone">Phone</th>
                            <th data-col-key="assigned_at">Assigned At</th>
                            <th data-col-key="current_status">Current Status</th>
                            <th data-col-key="quota_status">Quota Status</th>
                            <th data-col-key="actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($assignedLeads as $lead): ?>
                            <tr class="<?= $lead['completed_at'] ? 'table-success' : '' ?>">
                                <td data-col-key="lead_id">
                                    <a href="index.php?action=lead_view&id=<?= $lead['id'] ?>" class="text-decoration-none fw-bold">
                                        <?= htmlspecialchars($lead['lead_id']) ?>
                                    </a>
                                </td>
                                <td data-col-key="company"><?= htmlspecialchars($lead['company'] ?: 'N/A') ?></td>
                                <td data-col-key="contact_name"><?= htmlspecialchars($lead['contact_name'] ?: 'N/A') ?></td>
                                <td data-col-key="email">
                                    <?php if ($lead['email']): ?>
                                        <a href="mailto:<?= htmlspecialchars($lead['email']) ?>" class="text-decoration-none">
                                            <?= htmlspecialchars($lead['email']) ?>
                                        </a>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                                <td data-col-key="phone">
                                    <?php if ($lead['phone']): ?>
                                        <a href="tel:<?= htmlspecialchars($lead['phone']) ?>" class="text-decoration-none">
                                            <?= htmlspecialchars($lead['phone']) ?>
                                        </a>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                                <td data-col-key="assigned_at">
                                    <small class="text-muted">
                                        <?= date('M j, g:i A', strtotime($lead['assigned_at'])) ?>
                                    </small>
                                </td>
                                <td data-col-key="current_status">
                                    <span class="badge bg-secondary">
                                        <?= htmlspecialchars($lead['status_name'] ?: 'New Lead') ?>
                                    </span>
                                </td>
                                <td data-col-key="quota_status">
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
                                <td data-col-key="actions">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="index.php?action=lead_view&id=<?= $lead['id'] ?>" 
                                           class="btn btn-outline-primary" title="View Lead">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if (!$lead['completed_at']): ?>
                                            <button type="button" class="btn btn-outline-success mark-completed-btn" 
                                                    data-assignment-id="<?= $lead['assignment_id'] ?>" 
                                                    data-lead-id="<?= $lead['id'] ?>"
                                                    title="Mark as Completed">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-info update-status-btn" 
                                                    data-assignment-id="<?= $lead['assignment_id'] ?>" 
                                                    data-lead-id="<?= $lead['id'] ?>"
                                                    title="Update Status">
                                                <i class="fas fa-edit"></i>
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

<!-- Update Status Modal -->
<div class="modal fade" id="updateStatusModal" tabindex="-1" aria-labelledby="updateStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateStatusModalLabel">Update Lead Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="updateStatusForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="new_status_id" class="form-label">New Status</label>
                        <select class="form-select" id="new_status_id" name="new_status_id" required>
                            <option value="">Select Status</option>
                            <!-- Status options will be loaded dynamically -->
                        </select>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        This will update the lead status and mark it as completed in your quota.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
                <input type="hidden" name="lead_id" id="updateLeadId">
                <input type="hidden" name="assignment_id" id="updateAssignmentId">
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const updateStatusModal = new bootstrap.Modal(document.getElementById('updateStatusModal'));
    const updateStatusForm = document.getElementById('updateStatusForm');
    const refreshQuotaBtn = document.getElementById('refreshQuotaBtn');
    const columnsBtn = document.getElementById('columnsBtn');
    
    // Cookie helpers for column management
    function setCookie(name, value, days) {
        const d = new Date();
        d.setTime(d.getTime() + (days*24*60*60*1000));
        const expires = "expires=" + d.toUTCString();
        document.cookie = name + "=" + encodeURIComponent(value) + ";" + expires + ";path=/";
    }
    
    function getCookie(name) {
        const cname = name + "=";
        const ca = document.cookie.split(';');
        for (let i = 0; i < ca.length; i++) {
            let c = ca[i];
            while (c.charAt(0) === ' ') c = c.substring(1);
            if (c.indexOf(cname) === 0) return decodeURIComponent(c.substring(cname.length, c.length));
        }
        return "";
    }
    
    // Column management
    const COLUMNS_COOKIE = 'quota_columns';
    const allColumnKeys = Array.from(document.querySelectorAll('thead th[data-col-key]')).map(th => th.getAttribute('data-col-key'));
    
    function getColumnsSelection() {
        try { 
            const raw = getCookie(COLUMNS_COOKIE);
            if (!raw) return null;
            const arr = JSON.parse(raw);
            if (Array.isArray(arr) && arr.length) return arr;
            return null;
        } catch(e) { return null; }
    }
    
    function saveColumnsSelection(keys) {
        setCookie(COLUMNS_COOKIE, JSON.stringify(keys), 30);
    }
    
    function applyColumns(keys) {
        const show = new Set(keys);
        document.querySelectorAll('thead th[data-col-key]').forEach(th => {
            const key = th.getAttribute('data-col-key');
            th.style.display = show.has(key) ? '' : 'none';
        });
        document.querySelectorAll('tbody tr').forEach(tr => {
            tr.querySelectorAll('[data-col-key]').forEach(td => {
                const key = td.getAttribute('data-col-key');
                td.style.display = show.has(key) ? '' : 'none';
            });
        });
    }
    
    // Column management modal
    if (columnsBtn) {
        columnsBtn.addEventListener('click', function() {
            let modalEl = document.getElementById('columnsModal');
            if (!modalEl) {
                modalEl = document.createElement('div');
                modalEl.className = 'modal fade';
                modalEl.id = 'columnsModal';
                modalEl.tabIndex = -1;
                modalEl.innerHTML = `
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Choose Columns</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row" id="columnsList"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary" id="saveColumnsBtn">Save</button>
                        </div>
                    </div>
                </div>`;
                document.body.appendChild(modalEl);
            }
            const modal = new bootstrap.Modal(modalEl);
            const current = getColumnsSelection() || allColumnKeys;
            const list = modalEl.querySelector('#columnsList');
            list.innerHTML = '';
            allColumnKeys.forEach(key => {
                const label = key.replace(/_/g,' ').replace(/\b\w/g, c => c.toUpperCase());
                const id = 'col_' + key;
                const col = document.createElement('div');
                col.className = 'col-6 mb-2';
                col.innerHTML = `
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="${id}" value="${key}" ${current.includes(key)?'checked':''}>
                        <label class="form-check-label" for="${id}">${label}</label>
                    </div>`;
                list.appendChild(col);
            });
            modalEl.querySelector('#saveColumnsBtn').onclick = function() {
                const keys = Array.from(list.querySelectorAll('input:checked')).map(i => i.value);
                if (!keys.length) { alert('Please select at least one column'); return; }
                saveColumnsSelection(keys);
                applyColumns(keys);
                modal.hide();
            };
            modal.show();
        });
    }
    
    // Apply saved columns on load
    const savedCols = getColumnsSelection();
    if (savedCols) applyColumns(savedCols);
    
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Refresh quota button
    if (refreshQuotaBtn) {
        refreshQuotaBtn.addEventListener('click', function() {
            location.reload();
        });
    }
    
    // Handle mark as completed
    document.querySelectorAll('.mark-completed-btn').forEach(button => {
        button.addEventListener('click', function() {
            const assignmentId = this.getAttribute('data-assignment-id');
            
            if (confirm('Are you sure you want to mark this lead as completed?')) {
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
            }
        });
    });
    
    // Handle mark as not completed
    document.querySelectorAll('.mark-not-completed-btn').forEach(button => {
        button.addEventListener('click', function() {
            const assignmentId = this.getAttribute('data-assignment-id');
            
            if (confirm('Are you sure you want to mark this lead as not completed?')) {
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
            }
        });
    });
    
    // Handle update status button
    document.querySelectorAll('.update-status-btn').forEach(button => {
        button.addEventListener('click', function() {
            const leadId = this.getAttribute('data-lead-id');
            const assignmentId = this.getAttribute('data-assignment-id');
            
            document.getElementById('updateLeadId').value = leadId;
            document.getElementById('updateAssignmentId').value = assignmentId;
            
            // Load status options
            fetch('index.php?action=get_statuses')
                .then(response => response.json())
                .then(data => {
                    const statusSelect = document.getElementById('new_status_id');
                    statusSelect.innerHTML = '<option value="">Select Status</option>';
                    data.statuses.forEach(status => {
                        statusSelect.innerHTML += `<option value="${status.id}">${status.name}</option>`;
                    });
                })
                .catch(error => {
                    console.error('Error loading statuses:', error);
                });
            
            updateStatusModal.show();
        });
    });
    
    // Handle update status form submission
    updateStatusForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch('index.php?action=leads_quota_update_lead_status', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateStatusModal.hide();
                location.reload();
            } else {
                alert('Failed to update lead status: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating the status');
        });
    });
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
