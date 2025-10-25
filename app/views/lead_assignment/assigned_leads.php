<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-user-check me-2"></i>Assigned Leads</h2>
    <div>
        <a href="index.php?action=leads" class="btn btn-outline-secondary me-2">
            <i class="fas fa-arrow-left me-2"></i>Back to All Leads
        </a>
        <a href="index.php?action=export_assigned_leads<?= !empty($_GET) ? '&' . http_build_query($_GET) : '' ?>" class="btn btn-success">
            <i class="fas fa-download me-2"></i>Export CSV
        </a>
    </div>
</div>

<?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle me-2"></i>
        <?= htmlspecialchars($_SESSION['success_message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['success_message']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <?= htmlspecialchars($_SESSION['error_message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['error_message']); ?>
<?php endif; ?>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title"><?= number_format($statistics['total_assigned'] ?? 0) ?></h4>
                        <p class="card-text">Total Assigned</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-user-check fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title"><?= number_format($statistics['unique_assignees'] ?? 0) ?></h4>
                        <p class="card-text">Unique Assignees</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-users fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title"><?= number_format($statistics['unique_assigners'] ?? 0) ?></h4>
                        <p class="card-text">Unique Assigners</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-user-tie fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title"><?= number_format($totalLeads) ?></h4>
                        <p class="card-text">Current Page</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-list fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Search and Filters -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filters</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="index.php">
            <input type="hidden" name="action" value="assigned_leads">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="<?= htmlspecialchars($filters['search'] ?? '') ?>" 
                           placeholder="Search leads...">
                </div>
                <div class="col-md-3">
                    <label for="assigned_by" class="form-label">Assigned By</label>
                    <select class="form-select" id="assigned_by" name="assigned_by">
                        <option value="">All Assigners</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?= $user['id'] ?>" <?= ($filters['assigned_by'] ?? '') == $user['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($user['full_name'] ?: $user['username']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="assigned_to" class="form-label">Assigned To</label>
                    <select class="form-select" id="assigned_to" name="assigned_to">
                        <option value="">All Assignees</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?= $user['id'] ?>" <?= ($filters['assigned_to'] ?? '') == $user['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($user['full_name'] ?: $user['username']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="status_id" class="form-label">Status</label>
                    <select class="form-select" id="status_id" name="status_id">
                        <option value="">All Statuses</option>
                        <?php foreach ($statuses as $status): ?>
                            <option value="<?= $status['id'] ?>" <?= ($filters['status_id'] ?? '') == $status['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($status['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="assigned_date_from" class="form-label">Assigned Date From</label>
                    <input type="date" class="form-control" id="assigned_date_from" name="assigned_date_from" 
                           value="<?= htmlspecialchars($filters['assigned_date_from'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label for="assigned_date_to" class="form-label">Assigned Date To</label>
                    <input type="date" class="form-control" id="assigned_date_to" name="assigned_date_to" 
                           value="<?= htmlspecialchars($filters['assigned_date_to'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-2"></i>Apply Filters
                        </button>
                        <a href="index.php?action=assigned_leads" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i>Clear Filters
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Assigned Leads Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-list me-2"></i>Assigned Leads 
            <span class="badge bg-primary"><?= number_format($totalLeads) ?></span>
        </h5>
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="toggleColumnVisibility()">
                <i class="fas fa-columns me-1"></i>Columns
            </button>
            <button type="button" class="btn btn-outline-success btn-sm" onclick="location.reload()">
                <i class="fas fa-sync-alt me-1"></i>Refresh
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <?php if (empty($leads)): ?>
            <div class="text-center py-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No assigned leads found</h5>
                <p class="text-muted">Try adjusting your filters or check back later.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="assignedLeadsTable">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center">
                                <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                            </th>
                            <th class="sortable" data-column="lead_id">
                                Lead ID <i class="fas fa-sort"></i>
                            </th>
                            <th class="sortable" data-column="name">
                                Name <i class="fas fa-sort"></i>
                            </th>
                            <th class="sortable" data-column="company">
                                Company <i class="fas fa-sort"></i>
                            </th>
                            <th class="sortable" data-column="email">
                                Email <i class="fas fa-sort"></i>
                            </th>
                            <th class="sortable" data-column="phone">
                                Phone <i class="fas fa-sort"></i>
                            </th>
                            <th class="sortable" data-column="status_name">
                                Status <i class="fas fa-sort"></i>
                            </th>
                            <th class="sortable" data-column="assigned_to_name">
                                Assigned To <i class="fas fa-sort"></i>
                            </th>
                            <th class="sortable" data-column="assigned_by_name">
                                Assigned By <i class="fas fa-sort"></i>
                            </th>
                            <th class="sortable" data-column="assigned_at">
                                Assigned Date <i class="fas fa-sort"></i>
                            </th>
                            <th class="sortable" data-column="assignment_comment">
                                Comment <i class="fas fa-sort"></i>
                            </th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($leads as $lead): ?>
                            <tr>
                                <td class="text-center">
                                    <input type="checkbox" class="lead-checkbox" value="<?= $lead['id'] ?>">
                                </td>
                                <td>
                                    <a href="index.php?action=lead_view&id=<?= $lead['id'] ?>" class="text-decoration-none">
                                        <?= htmlspecialchars($lead['lead_id']) ?>
                                    </a>
                                </td>
                                <td><?= htmlspecialchars($lead['name'] ?? '') ?></td>
                                <td><?= htmlspecialchars($lead['company'] ?? '') ?></td>
                                <td>
                                    <?php if (!empty($lead['email'])): ?>
                                        <a href="mailto:<?= htmlspecialchars($lead['email']) ?>" class="text-decoration-none">
                                            <?= htmlspecialchars($lead['email']) ?>
                                        </a>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($lead['phone'])): ?>
                                        <a href="tel:<?= htmlspecialchars($lead['phone']) ?>" class="text-decoration-none">
                                            <?= htmlspecialchars($lead['phone']) ?>
                                        </a>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-secondary"><?= htmlspecialchars($lead['status_name'] ?? '') ?></span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-user me-2 text-primary"></i>
                                        <div>
                                        <div class="fw-bold"><?= htmlspecialchars($lead['assigned_to_name'] ?? '') ?></div>
                                        <small class="text-muted">
                                            <?= htmlspecialchars($lead['assigned_to_full_name'] ?? '') ?>
                                        </small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-user-tie me-2 text-info"></i>
                                        <div>
                                        <div class="fw-bold"><?= htmlspecialchars($lead['assigned_by_name'] ?? '') ?></div>
                                        <small class="text-muted">
                                            <?= htmlspecialchars($lead['assigned_by_full_name'] ?? '') ?>
                                        </small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-calendar me-2 text-success"></i>
                                        <div>
                                            <div><?= date('M j, Y', strtotime($lead['assigned_at'])) ?></div>
                                            <small class="text-muted"><?= date('g:i A', strtotime($lead['assigned_at'])) ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if (!empty($lead['assignment_comment'])): ?>
                                        <span class="text-truncate d-inline-block" style="max-width: 150px;" 
                                              title="<?= htmlspecialchars($lead['assignment_comment']) ?>">
                                            <?= htmlspecialchars($lead['assignment_comment']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">No comment</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <a href="index.php?action=lead_view&id=<?= $lead['id'] ?>" 
                                           class="btn btn-sm btn-outline-primary" title="View Lead">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-info" 
                                                onclick="viewAssignmentHistory(<?= $lead['id'] ?>)" title="Assignment History">
                                            <i class="fas fa-history"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-warning" 
                                                onclick="reassignLead(<?= $lead['id'] ?>)" title="Reassign Lead">
                                            <i class="fas fa-user-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                onclick="unassignLead(<?= $lead['id'] ?>)" title="Unassign Lead">
                                            <i class="fas fa-user-times"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Bulk Actions -->
<div class="mt-3" id="bulkActions" style="display: none;">
    <div class="card">
        <div class="card-body">
            <div class="d-flex align-items-center gap-3">
                <span class="fw-bold">Bulk Actions:</span>
                <button type="button" class="btn btn-primary btn-sm" onclick="bulkReassign()">
                    <i class="fas fa-user-edit me-1"></i>Reassign Selected
                </button>
                <button type="button" class="btn btn-danger btn-sm" onclick="bulkUnassign()">
                    <i class="fas fa-user-times me-1"></i>Unassign Selected
                </button>
                <span class="text-muted" id="selectedCount">0 leads selected</span>
            </div>
        </div>
    </div>
</div>

<!-- Pagination -->
<?php if ($totalPages > 1): ?>
    <nav aria-label="Assigned leads pagination" class="mt-4">
        <ul class="pagination justify-content-center">
            <?php if ($page > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?action=assigned_leads&page=<?= $page - 1 ?><?= !empty($_GET) ? '&' . http_build_query(array_diff_key($_GET, ['page' => ''])) : '' ?>">
                        <i class="fas fa-chevron-left"></i> Previous
                    </a>
                </li>
            <?php endif; ?>

            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                    <a class="page-link" href="?action=assigned_leads&page=<?= $i ?><?= !empty($_GET) ? '&' . http_build_query(array_diff_key($_GET, ['page' => ''])) : '' ?>">
                        <?= $i ?>
                    </a>
                </li>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <li class="page-item">
                    <a class="page-link" href="?action=assigned_leads&page=<?= $page + 1 ?><?= !empty($_GET) ? '&' . http_build_query(array_diff_key($_GET, ['page' => ''])) : '' ?>">
                        Next <i class="fas fa-chevron-right"></i>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
<?php endif; ?>

<!-- Assignment History Modal -->
<div class="modal fade" id="assignmentHistoryModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-history me-2"></i>Assignment History
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="assignmentHistoryContent">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reassign Lead Modal -->
<div class="modal fade" id="reassignModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user-edit me-2"></i>Reassign Lead
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="reassignForm">
                <div class="modal-body">
                    <input type="hidden" id="reassignLeadId" name="lead_id">
                    <div class="mb-3">
                        <label for="reassignTo" class="form-label">Assign To</label>
                        <select class="form-select" id="reassignTo" name="assigned_to" required>
                            <option value="">Select User</option>
                            <?php foreach ($users as $user): ?>
                            <option value="<?= $user['id'] ?>">
                                <?= htmlspecialchars($user['full_name'] ?: $user['username']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="reassignComment" class="form-label">Comment</label>
                        <textarea class="form-control" id="reassignComment" name="comment" rows="3" 
                                  placeholder="Add a comment about this assignment..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-user-edit me-1"></i>Reassign Lead
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Reassign Modal -->
<div class="modal fade" id="bulkReassignModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user-edit me-2"></i>Bulk Reassign Leads
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="bulkReassignForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="bulkReassignTo" class="form-label">Assign To</label>
                        <select class="form-select" id="bulkReassignTo" name="assigned_to" required>
                            <option value="">Select User</option>
                            <?php foreach ($users as $user): ?>
                            <option value="<?= $user['id'] ?>">
                                <?= htmlspecialchars($user['full_name'] ?: $user['username']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="bulkReassignComment" class="form-label">Comment</label>
                        <textarea class="form-control" id="bulkReassignComment" name="comment" rows="3" 
                                  placeholder="Add a comment about this assignment..."></textarea>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <span id="bulkReassignCount">0</span> leads will be reassigned.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-user-edit me-1"></i>Reassign Leads
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Column visibility management
function toggleColumnVisibility() {
    // Implementation for column visibility toggle
    alert('Column visibility feature will be implemented');
}

// Select all functionality
function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.lead-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
    
    updateBulkActions();
}

// Update bulk actions visibility
function updateBulkActions() {
    const checkboxes = document.querySelectorAll('.lead-checkbox:checked');
    const bulkActions = document.getElementById('bulkActions');
    const selectedCount = document.getElementById('selectedCount');
    
    if (checkboxes.length > 0) {
        bulkActions.style.display = 'block';
        selectedCount.textContent = `${checkboxes.length} lead${checkboxes.length > 1 ? 's' : ''} selected`;
    } else {
        bulkActions.style.display = 'none';
    }
}

// Add event listeners to checkboxes
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.lead-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkActions);
    });
});

// View assignment history
function viewAssignmentHistory(leadId) {
    const modal = new bootstrap.Modal(document.getElementById('assignmentHistoryModal'));
    const content = document.getElementById('assignmentHistoryContent');
    
    content.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>';
    modal.show();
    
    fetch(`index.php?action=get_assignment_history&lead_id=${leadId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let html = '<div class="timeline">';
                data.history.forEach(assignment => {
                    html += `
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">
                                    Assigned to ${assignment.assigned_to_name || 'Unknown'}
                                </h6>
                                <p class="text-muted mb-1">
                                    By ${assignment.assigned_by_name || 'Unknown'} on 
                                    ${new Date(assignment.assigned_at).toLocaleString()}
                                </p>
                                    ${assignment.comment ? `<p class="mb-0"><strong>Comment:</strong> ${assignment.comment}</p>` : ''}
                            </div>
                        </div>
                    `;
                });
                html += '</div>';
                content.innerHTML = html;
            } else {
                content.innerHTML = '<div class="alert alert-warning">No assignment history found.</div>';
            }
        })
        .catch(error => {
            content.innerHTML = '<div class="alert alert-danger">Error loading assignment history.</div>';
        });
}

// Reassign lead
function reassignLead(leadId) {
    document.getElementById('reassignLeadId').value = leadId;
    const modal = new bootstrap.Modal(document.getElementById('reassignModal'));
    modal.show();
}

// Bulk reassign
function bulkReassign() {
    const selectedLeads = Array.from(document.querySelectorAll('.lead-checkbox:checked')).map(cb => cb.value);
    if (selectedLeads.length === 0) {
        alert('Please select leads to reassign.');
        return;
    }
    
    document.getElementById('bulkReassignCount').textContent = selectedLeads.length;
    const modal = new bootstrap.Modal(document.getElementById('bulkReassignModal'));
    modal.show();
}

// Unassign lead
function unassignLead(leadId) {
    if (confirm('Are you sure you want to unassign this lead?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'index.php?action=unassign_lead';
        
        const leadIdInput = document.createElement('input');
        leadIdInput.type = 'hidden';
        leadIdInput.name = 'lead_id';
        leadIdInput.value = leadId;
        
        const redirectInput = document.createElement('input');
        redirectInput.type = 'hidden';
        redirectInput.name = 'redirect_url';
        redirectInput.value = window.location.href;
        
        form.appendChild(leadIdInput);
        form.appendChild(redirectInput);
        document.body.appendChild(form);
        form.submit();
    }
}

// Bulk unassign
function bulkUnassign() {
    const selectedLeads = Array.from(document.querySelectorAll('.lead-checkbox:checked')).map(cb => cb.value);
    if (selectedLeads.length === 0) {
        alert('Please select leads to unassign.');
        return;
    }
    
    if (confirm(`Are you sure you want to unassign ${selectedLeads.length} leads?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'index.php?action=bulk_unassign_leads';
        
        selectedLeads.forEach(leadId => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'lead_ids[]';
            input.value = leadId;
            form.appendChild(input);
        });
        
        const redirectInput = document.createElement('input');
        redirectInput.type = 'hidden';
        redirectInput.name = 'redirect_url';
        redirectInput.value = window.location.href;
        form.appendChild(redirectInput);
        
        document.body.appendChild(form);
        form.submit();
    }
}

// Form submissions
document.getElementById('reassignForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('redirect_url', window.location.href);
    
    fetch('index.php?action=assign_lead', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (response.ok) {
            location.reload();
        } else {
            alert('Error reassigning lead. Please try again.');
        }
    });
});

document.getElementById('bulkReassignForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const selectedLeads = Array.from(document.querySelectorAll('.lead-checkbox:checked')).map(cb => cb.value);
    const formData = new FormData(this);
    
    selectedLeads.forEach(leadId => {
        formData.append('lead_ids[]', leadId);
    });
    formData.append('redirect_url', window.location.href);
    
    fetch('index.php?action=bulk_assign_leads', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (response.ok) {
            location.reload();
        } else {
            alert('Error reassigning leads. Please try again.');
        }
    });
});
</script>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -35px;
    top: 5px;
    width: 10px;
    height: 10px;
    border-radius: 50%;
}

.timeline-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border-left: 3px solid #007bff;
}

.sortable {
    cursor: pointer;
    user-select: none;
}

.sortable:hover {
    background-color: #f8f9fa;
}

.table-responsive {
    max-height: 600px;
    overflow-y: auto;
}
</style>

<?php include __DIR__ . '/../layout/footer.php'; ?>
