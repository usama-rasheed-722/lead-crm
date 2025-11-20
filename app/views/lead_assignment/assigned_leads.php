<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-user-check me-2"></i>
        <?php if (auth_user()['role'] === 'sdr'): ?>
            My Assigned Leads
        <?php else: ?>
            Assigned Leads
        <?php endif; ?>
    </h2>
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
<div class="search-form mb-4">
        <form method="GET" action="index.php">
            <input type="hidden" name="action" value="assigned_leads">
            <div class="row g-3">
                <div class="col-md-3">
                <label for="search" class="form-label">Search All Fields</label>
                    <input type="text" class="form-control" id="search" name="search" 
                       value="<?= htmlspecialchars($search ?? '') ?>" 
                       placeholder="Search across all fields...">
            </div>
            <div class="col-md-3">
                <label for="field_search" class="form-label">Search Specific Field</label>
                <div class="input-group">
                    <select class="form-select" id="field_type" name="field_type" style="max-width: 150px;">
                        <option value="">Select Field</option>
                        <?php if (!empty($availableFields)): foreach ($availableFields as $field): ?>
                            <option value="<?= htmlspecialchars($field['value']) ?>" <?= ($filters['field_type'] ?? '') === $field['value'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($field['label']) ?>
                            </option>
                        <?php endforeach; endif; ?>
                    </select>
                    <input type="text" class="form-control" id="field_value" name="field_value" 
                           value="<?= htmlspecialchars($filters['field_value'] ?? '') ?>" 
                           placeholder="Enter value...">
                </div>
            </div>
            <div class="col-md-2">
                <label for="sdr_id" class="form-label">SDR</label>
                <select class="form-select" id="sdr_id" name="sdr_id">
                    <option value="">All SDRs</option>
                    <?php foreach ($users as $user): ?>
                        <?php if ($user['role'] === 'sdr'): ?>
                            <option value="<?= $user['sdr_id'] ?? $user['id'] ?>" <?= ($filters['sdr_id'] ?? '') == ($user['sdr_id'] ?? $user['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($user['full_name'] ?: $user['username']) ?>
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label for="duplicate_status" class="form-label">Duplicate</label>
                <select class="form-select" id="duplicate_status" name="duplicate_status">
                    <option value="">All</option>
                    <option value="unique" <?= ($filters['duplicate_status'] ?? '') === 'unique' ? 'selected' : '' ?>>✅ Unique</option>
                    <option value="duplicate" <?= ($filters['duplicate_status'] ?? '') === 'duplicate' ? 'selected' : '' ?>>🔁 Duplicate</option>
                    <option value="incomplete" <?= ($filters['duplicate_status'] ?? '') === 'incomplete' ? 'selected' : '' ?>>⚠️ Incomplete</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="status_id" class="form-label">Status</label>
                <select class="form-select" id="status_id" name="status_id">
                    <option value="">All Statuses</option>
                    <?php if (!empty($statuses)): foreach ($statuses as $st): ?>
                        <option value="<?= $st['id'] ?>" <?= (isset($filters['status_id']) && $filters['status_id'] == $st['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($st['name']) ?>
                        </option>
                    <?php endforeach; endif; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label for="lead_source_id" class="form-label">Lead Source</label>
                <select class="form-select" id="lead_source_id" name="lead_source_id">
                    <option value="">All Sources</option>
                    <?php if (!empty($leadSources)): foreach ($leadSources as $source): ?>
                        <option value="<?= htmlspecialchars($source['id']) ?>" 
                                <?= ($filters['lead_source_id'] ?? '') == $source['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($source['name']) ?>
                        </option>
                    <?php endforeach; endif; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label for="date_from" class="form-label">From Date</label>
                <input type="date" class="form-control" id="date_from" name="date_from" 
                       value="<?= htmlspecialchars($filters['date_from'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <label for="date_to" class="form-label">To Date</label>
                <input type="date" class="form-control" id="date_to" name="date_to" 
                       value="<?= htmlspecialchars($filters['date_to'] ?? '') ?>">
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
                <?php if (auth_user()['role'] !== 'sdr'): ?>
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
                <?php endif; ?>
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
        </div>
        <div class="row mt-3">
            <div class="col-12">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-search me-2"></i>Search
                        </button>
                        <a href="index.php?action=reset_assigned_leads_filters" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-2"></i>Clear
                        </a>
                </div>
            </div>
        </form>
</div>

<!-- Assigned Leads Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Assigned Leads (<?= number_format($totalLeads) ?> found)</h5>
        <div>
            <button type="button" class="btn btn-sm btn-outline-warning me-2" id="clearSelectionBtn" disabled>
                <i class="fas fa-times me-1"></i><span id="selectionCount">0</span>  Clear Selection
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary me-2" id="columnsBtn">
                <i class="fas fa-columns me-1"></i>Columns
            </button>
            <button type="button" class="btn btn-sm btn-primary me-2" id="bulkUpdateBtn" disabled>
                <i class="fas fa-edit me-1"></i>Bulk Update Status
            </button>
            <button type="button" class="btn btn-sm btn-info me-2" id="bulkReassignBtn" disabled>
                <i class="fas fa-user-edit me-1"></i>Reassign Selected
            </button>
            <?php if (auth_user()['role'] !== 'sdr'): ?>
            <button type="button" class="btn btn-sm btn-danger me-2" id="bulkUnassignBtn" disabled>
                <i class="fas fa-user-times me-1"></i>Unassign Selected
            </button>
            <?php endif; ?>
            <a href="index.php?action=export_assigned_leads&<?= http_build_query(array_merge($filters ?? [], ['search' => $search ?? ''])) ?>" class="btn btn-sm btn-outline-success me-2">
                <i class="fas fa-download me-1"></i>Export CSV
            </a>
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
                                <input type="checkbox" id="selectAll" class="form-check-input">
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
                            <th class="sortable" data-column="lead_segment">
                                Lead Segment <i class="fas fa-sort"></i>
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
                            <?php if (auth_user()['role'] !== 'sdr'): ?>
                            <th class="sortable" data-column="assigned_to_name">
                                Assigned To <i class="fas fa-sort"></i>
                            </th>
                            <?php endif; ?>
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
                                    <input type="checkbox" class="form-check-input lead-checkbox" value="<?= $lead['id'] ?>">
                                </td>
                                <td data-column="lead_id">
                                    <a href="index.php?action=lead_view&id=<?= $lead['id'] ?>" class="text-decoration-none">
                                        <?= htmlspecialchars($lead['lead_id']) ?>
                                    </a>
                                </td>
                                <td data-column="name"><?= htmlspecialchars($lead['name'] ?? '') ?></td>
                                <td data-column="company"><?= htmlspecialchars($lead['company'] ?? '') ?></td>
                                <td data-column="lead_segment"><?= htmlspecialchars($lead['lead_segment'] ?? '') ?></td>
                                <td data-column="email">
                                    <?php if (!empty($lead['email'])): ?>
                                        <div class="d-flex align-items-center gap-1">
                                            <a href="mailto:<?= htmlspecialchars($lead['email']) ?>" class="text-decoration-none">
                                                <?= htmlspecialchars($lead['email']) ?>
                                            </a>
                                            <?php if ($lead['email_verified'] ?? 0): ?>
                                                <span class="badge bg-success" title="Verified">
                                                    <i class="fas fa-check-circle"></i>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td data-column="phone">
                                    <?php if (!empty($lead['phone'])): ?>
                                        <div class="d-flex align-items-center gap-1">
                                            <a href="tel:<?= htmlspecialchars($lead['phone']) ?>" class="text-decoration-none">
                                                <?= htmlspecialchars($lead['phone']) ?>
                                            </a>
                                            <?php if ($lead['phone_verified'] ?? 0): ?>
                                                <span class="badge bg-success" title="Verified">
                                                    <i class="fas fa-check-circle"></i>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td data-column="status_name">
                                    <span class="badge bg-secondary"><?= htmlspecialchars($lead['status_name'] ?? '') ?></span>
                                </td>
                                <?php if (auth_user()['role'] !== 'sdr'): ?>
                                <td data-column="assigned_to_name">
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
                                <?php endif; ?>
                                <td data-column="assigned_by_name">
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
                                <td data-column="assigned_at">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-calendar me-2 text-success"></i>
                                        <div>
                                            <div><?= date('M j, Y', strtotime($lead['assigned_at'])) ?></div>
                                            <small class="text-muted"><?= date('g:i A', strtotime($lead['assigned_at'])) ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td data-column="assignment_comment">
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
                                        <?php if ($lead['assignment_id']): ?>
                                            <?php if (!$lead['completed_at']): ?>
                                                <button type="button" class="btn btn-outline-success mark-completed-btn" 
                                                        data-assignment-id="<?= $lead['assignment_id'] ?>" 
                                                        data-lead-id="<?= $lead['id'] ?>"
                                                        title="Mark as Completed">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            <?php else: ?>
                                                <button type="button" class="btn btn-outline-warning mark-not-completed-btn" 
                                                        data-assignment-id="<?= $lead['assignment_id'] ?>" title="Mark as Not Completed">
                                                    <i class="fas fa-undo"></i>
                                                </button>
                                            <?php endif; ?>
                                        <?php endif; ?>

                                        <?php if (auth_user()['role'] !== 'sdr'): ?>
                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                onclick="unassignLead(<?= $lead['id'] ?>)" title="Unassign Lead">
                                            <i class="fas fa-user-times"></i>
                                        </button>
                                        <?php endif; ?>
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

<!-- Bulk Update Status Modal -->
<div class="modal fade" id="bulkUpdateModal" tabindex="-1" aria-labelledby="bulkUpdateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bulkUpdateModalLabel">Bulk Update Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="bulkUpdateForm" method="POST" action="index.php?action=bulk_update_status">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="new_status_id" class="form-label">New Status</label>
                        <select class="form-select" id="new_status_id" name="new_status_id" required>
                            <option value="">Select Status</option>
                            <?php if (!empty($statuses)): foreach ($statuses as $st): ?>
                                <?php
                                $statusModel = new StatusModel();
                                $customFields = $statusModel->getCustomFieldsByName($st['name']);
                                $hasFields = count($customFields) > 0;
                                ?>
                                <option value="<?= $st['id'] ?>" data-has-fields="<?= $hasFields ? 'true' : 'false' ?>">
                                    <?= htmlspecialchars($st['name']) ?><?= $hasFields ? ' 📝' : '' ?>
                                </option>
                            <?php endforeach; endif; ?>
                        </select>
                        <div class="form-text">
                            <i class="fas fa-info-circle text-info me-1"></i>
                            <span class="text-muted">📝 indicates statuses that require additional information</span>
                        </div>
                    </div>
                    
                    <!-- Dynamic Custom Fields Container -->
                    <div id="bulkCustomFieldsContainer"></div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        This will update the status for <span id="bulkUpdateSelectedCount">0</span> selected lead(s).
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
                <input type="hidden" name="lead_ids" id="bulkUpdateIds">
                <input type="hidden" name="redirect_url" id="bulkUpdateRedirect">
            </form>
        </div>
    </div>
</div>

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
                        <label for="reassignQuotaSingle" class="form-label">Assign Quota</label>
                        <input type="checkbox" id="reassignQuotaSingle" name="assign_quota" value="0">
                    </div>
                    <div class="mb-3">
                        <label for="reassignDateSingle" class="form-label">Date</label>
                        <input type="date" class="form-control" id="reassignDateSingle" name="date" disabled>
                    </div>
                    <div class="mb-3">
                        <label for="reassignCommentSingle" class="form-label">Comment</label>
                        <textarea class="form-control" id="reassignCommentSingle" name="comment" rows="3" 
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
                        <label for="reassignDate" class="form-label">Assign Quota</label>
                        <input type="checkbox" id="reassignQuota" name="assign_quota" value="0">
                    </div>
                    <div class="mb-3">
                        <label for="reassignDate" class="form-label">Date</label>
                        <input type="date" disabled class="form-control" id="reassignDate" name="date">
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
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const leadCheckboxes = document.querySelectorAll('.lead-checkbox');
    const bulkUpdateBtn = document.getElementById('bulkUpdateBtn');
    const bulkReassignBtn = document.getElementById('bulkReassignBtn');
    const bulkUnassignBtn = document.getElementById('bulkUnassignBtn');
    const clearSelectionBtn = document.getElementById('clearSelectionBtn');
    const selectionCount = document.getElementById('selectionCount');
    
    // Cookie helpers
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

    // Persistent selection across pages
    const SELECT_COOKIE = 'selected_assigned_lead_ids';
    function getSelectedSet() {
        try { return new Set(JSON.parse(getCookie(SELECT_COOKIE) || '[]')); } catch(e) { return new Set(); }
    }
    
    function saveSelectedSet(set) {
        setCookie(SELECT_COOKIE, JSON.stringify(Array.from(set)), 7);
    }
    
    const selectedSet = getSelectedSet();

    // Restore selection state on current page
    leadCheckboxes.forEach(cb => {
        if (selectedSet.has(cb.value)) cb.checked = true;
    });
    updateBulkButtons();
    updateSelectAllState();

// Select all functionality
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            leadCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
                if (this.checked) { selectedSet.add(checkbox.value); }
                else { selectedSet.delete(checkbox.value); }
            });
            saveSelectedSet(selectedSet);
            updateBulkButtons();
        });
    }

    // Individual checkbox change
    leadCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            if (this.checked) { selectedSet.add(this.value); } else { selectedSet.delete(this.value); }
            saveSelectedSet(selectedSet);
            updateBulkButtons();
            updateSelectAllState();
        });
    });

    function updateBulkButtons() {
        const count = selectedSet.size;
        if (bulkUpdateBtn) bulkUpdateBtn.disabled = count === 0;
        if (bulkReassignBtn) bulkReassignBtn.disabled = count === 0;
        if (bulkUnassignBtn) bulkUnassignBtn.disabled = count === 0;
        if (clearSelectionBtn) clearSelectionBtn.disabled = count === 0;
        if (selectionCount) selectionCount.textContent = count;
    }

    function updateSelectAllState() {
        const checkedBoxes = document.querySelectorAll('.lead-checkbox:checked');
        const totalBoxes = leadCheckboxes.length;
        
        if (selectAllCheckbox) {
            if (checkedBoxes.length === 0) {
                selectAllCheckbox.indeterminate = false;
                selectAllCheckbox.checked = false;
            } else if (checkedBoxes.length === totalBoxes) {
                selectAllCheckbox.indeterminate = false;
                selectAllCheckbox.checked = true;
    } else {
                selectAllCheckbox.indeterminate = true;
            }
        }
    }

    // Clear selection functionality
    if (clearSelectionBtn) {
        clearSelectionBtn.addEventListener('click', function() {
            selectedSet.clear();
            saveSelectedSet(selectedSet);
            
            // Uncheck all checkboxes on current page
            leadCheckboxes.forEach(cb => cb.checked = false);
            if (selectAllCheckbox) selectAllCheckbox.checked = false;
            
            updateBulkButtons();
            updateSelectAllState();
        });
    }

    // Initialize all modals once on page load
    const bulkUpdateModalElement = document.getElementById('bulkUpdateModal');
    const bulkReassignModalElement = document.getElementById('bulkReassignModal');
    const assignmentHistoryModalElement = document.getElementById('assignmentHistoryModal');
    const reassignModalElement = document.getElementById('reassignModal');
    
    let bulkUpdateModalInstance = null;
    let bulkReassignModalInstance = null;
    let assignmentHistoryModalInstance = null;
    let reassignModalInstance = null;
    
    if (bulkUpdateModalElement) {
        bulkUpdateModalInstance = new bootstrap.Modal(bulkUpdateModalElement, {
            backdrop: true,
            keyboard: true
        });
    }
    
    if (bulkReassignModalElement) {
        bulkReassignModalInstance = new bootstrap.Modal(bulkReassignModalElement, {
            backdrop: true,
            keyboard: true
        });
    }
    
    if (assignmentHistoryModalElement) {
        assignmentHistoryModalInstance = new bootstrap.Modal(assignmentHistoryModalElement, {
            backdrop: true,
            keyboard: true
        });
    }
    
    if (reassignModalElement) {
        reassignModalInstance = new bootstrap.Modal(reassignModalElement, {
            backdrop: true,
            keyboard: true
        });
    }

    // Bulk update functionality
    if (bulkUpdateBtn && bulkUpdateModalInstance) {
        bulkUpdateBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const ids = Array.from(selectedSet);
            if (ids.length === 0) return;
            
            // Update form fields if they exist
            const bulkUpdateIds = document.getElementById('bulkUpdateIds');
            const bulkUpdateSelectedCount = document.getElementById('bulkUpdateSelectedCount');
            const bulkUpdateRedirect = document.getElementById('bulkUpdateRedirect');
            
            if (bulkUpdateIds) {
                bulkUpdateIds.value = ids.join(',');
            }
            if (bulkUpdateSelectedCount) {
                bulkUpdateSelectedCount.textContent = ids.length;
            }
            if (bulkUpdateRedirect) {
                bulkUpdateRedirect.value = window.location.href;
            }
            
            // Show the modal
            bulkUpdateModalInstance.show();
        });
    }

    // Bulk reassign functionality
    if (bulkReassignBtn && bulkReassignModalInstance) {
        bulkReassignBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const ids = Array.from(selectedSet);
            if (ids.length === 0) return;
            
            // Update the count display if element exists
            const bulkReassignCount = document.getElementById('bulkReassignCount');
            if (bulkReassignCount) {
                bulkReassignCount.textContent = ids.length;
            }
            
            // Show the modal
            bulkReassignModalInstance.show();
        });
    }

    // Bulk unassign functionality
    if (bulkUnassignBtn) {
        bulkUnassignBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const ids = Array.from(selectedSet);
            if (ids.length === 0) return;
            if (confirm(`Are you sure you want to unassign ${ids.length} leads?`)) {
                try {
                    const bulkUnassignIds = document.getElementById('bulkUnassignIds');
                    const bulkUnassignRedirect = document.getElementById('bulkUnassignRedirect');
                    
                    if (!bulkUnassignIds || !bulkUnassignRedirect) {
                        console.error('Bulk unassign form elements not found');
                        return;
                    }
                    
                  
                } catch(error) {
                    console.error('Error in bulk unassign:', error);
                }
            }
        });
    }

    // Handle bulk update status change with custom fields
    const bulkStatusSelect = document.getElementById('new_status_id');
    const bulkCustomFieldsContainer = document.getElementById('bulkCustomFieldsContainer');
    
    if (bulkStatusSelect && bulkCustomFieldsContainer) {
        bulkStatusSelect.addEventListener('change', function() {
            const selectedStatusId = this.value;
            const selectedStatusName = this.selectedOptions[0]?.text?.replace(' 📝', '') || '';
            
            // Clear previous custom fields
            bulkCustomFieldsContainer.innerHTML = '';
            
            if (selectedStatusId) {
                // Show loading indicator
                bulkCustomFieldsContainer.innerHTML = '<div class="text-center py-3"><i class="fas fa-spinner fa-spin me-2"></i>Loading custom fields...</div>';
                
                // Fetch custom fields for the selected status
                fetch(`index.php?action=get_custom_fields_for_status&status_id=${selectedStatusId}`)
                    .then(response => response.json())
                    .then(data => {
                        bulkCustomFieldsContainer.innerHTML = '';
                        
                        if (data.customFields && data.customFields.length > 0) {
                            // Add header for custom fields
                            bulkCustomFieldsContainer.insertAdjacentHTML('beforeend', '<div class="alert alert-info mb-3"><i class="fas fa-info-circle me-2"></i>This status requires additional information for all selected leads:</div>');
                            
                            data.customFields.forEach(field => {
                                const fieldHtml = createCustomFieldHtml(field);
                                bulkCustomFieldsContainer.insertAdjacentHTML('beforeend', fieldHtml);
                            });
                        } else {
                            // Show message when no custom fields
                            bulkCustomFieldsContainer.insertAdjacentHTML('beforeend', '<div class="alert alert-success mb-3"><i class="fas fa-check-circle me-2"></i>No additional fields required for this status.</div>');
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching custom fields:', error);
                        bulkCustomFieldsContainer.innerHTML = '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i>Error loading custom fields. Please try again.</div>';
                    });
            }
        });
    }
    
    // Reset bulk update modal when closed
    if (bulkUpdateModalElement) {
        bulkUpdateModalElement.addEventListener('hidden.bs.modal', function () {
            if (bulkStatusSelect) bulkStatusSelect.value = '';
            if (bulkCustomFieldsContainer) bulkCustomFieldsContainer.innerHTML = '';
            const form = document.getElementById('bulkUpdateForm');
            if (form) form.reset();
        });
    }
    
    // Reset bulk reassign modal when closed
    if (bulkReassignModalElement) {
        bulkReassignModalElement.addEventListener('hidden.bs.modal', function () {
            const form = document.getElementById('bulkReassignForm');
            if (form) form.reset();
        });
    }
    
    // Function to create custom field HTML
    function createCustomFieldHtml(field) {
        const required = field.is_required ? 'required' : '';
        const requiredAsterisk = field.is_required ? ' <span class="text-danger">*</span>' : '';
        
        let inputHtml = '';
        
        switch (field.field_type) {
            case 'textarea':
                inputHtml = `<textarea class="form-control" name="custom_field_${field.field_name}" ${required}></textarea>`;
                break;
            case 'select':
                const options = field.field_options ? field.field_options.split('\n') : [];
                inputHtml = `<select class="form-select" name="custom_field_${field.field_name}" ${required}>`;
                inputHtml += '<option value="">Select...</option>';
                options.forEach(option => {
                    inputHtml += `<option value="${option.trim()}">${option.trim()}</option>`;
                });
                inputHtml += '</select>';
                break;
            case 'date':
                inputHtml = `<input type="date" class="form-control" name="custom_field_${field.field_name}" ${required}>`;
                break;
            case 'number':
                inputHtml = `<input type="number" class="form-control" name="custom_field_${field.field_name}" ${required}>`;
                break;
            case 'email':
                inputHtml = `<input type="email" class="form-control" name="custom_field_${field.field_name}" ${required}>`;
                break;
            case 'url':
                inputHtml = `<input type="url" class="form-control" name="custom_field_${field.field_name}" ${required}>`;
                break;
            default: // text
                inputHtml = `<input type="text" class="form-control" name="custom_field_${field.field_name}" ${required}>`;
        }
        
        return `
            <div class="custom-field-container">
                <label for="custom_field_${field.field_name}" class="form-label fw-bold">
                    ${field.field_label}${requiredAsterisk}
                </label>
                ${inputHtml}
                ${field.is_required ? '<div class="form-text text-muted mt-2"><i class="fas fa-asterisk text-danger me-1"></i>This field is required</div>' : ''}
            </div>
        `;
    }

    // Bulk update form submission
    const bulkUpdateForm = document.getElementById('bulkUpdateForm');
    if (bulkUpdateForm) {
        bulkUpdateForm.addEventListener('submit', function(e) {
            const newStatusId = document.getElementById('new_status_id').value;
            const newStatusName = document.getElementById('new_status_id').selectedOptions[0]?.text?.replace(' 📝', '') || '';
            const count = selectedSet.size;
            if (!newStatusId || count === 0) {
                e.preventDefault();
                alert('Select leads and a status');
                return;
            }
            if (!confirm(`Are you sure you want to update the status to "${newStatusName}" for ${count} selected lead(s)?`)) {
                e.preventDefault();
            } else {
                // Clear selections after successful submission
                setTimeout(() => {
                    selectedSet.clear();
                    saveSelectedSet(selectedSet);
                    leadCheckboxes.forEach(cb => cb.checked = false);
                    if (selectAllCheckbox) selectAllCheckbox.checked = false;
                    updateBulkButtons();
                    updateSelectAllState();
                }, 1000);
            }
        });
    }

    document.getElementById('reassignQuota').addEventListener('change', function() {
        const dateInput = document.getElementById('reassignDate');
        if (!this.checked) {
              dateInput.value = '';
            dateInput.disabled = true;
        } else {
            dateInput.value = new Date().toISOString().split('T')[0];
            dateInput.disabled = false;
        }
    });


    document.getElementById('reassignQuotaSingle').addEventListener('change', function() {
        const dateInput = document.getElementById('reassignDateSingle');
        if (!this.checked) {
            dateInput.value = '';
            dateInput.disabled = true;
        } else {
            dateInput.value = new Date().toISOString().split('T')[0];
            dateInput.disabled = false;   
        }
    });

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

    // Bulk reassign form submission
    const bulkReassignForm = document.getElementById('bulkReassignForm');
    if (bulkReassignForm) {
        bulkReassignForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const ids = Array.from(selectedSet);
            if (ids.length === 0) { 
                return;
            }
            const formData = new FormData(this);
            ids.forEach(leadId => {
                formData.append('lead_ids[]', leadId);
            });
            formData.append('date', document.getElementById('reassignDate')?.value);
            formData.append('reassign_quota', document.getElementById('reassignQuota')?.checked ? 1 : 0);
            formData.append('comment', document.getElementById('bulkReassignComment').value);
            formData.append('redirect_url', window.location.href);
            fetch('index.php?action=bulk_assign_leads', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (response.ok) {
                    console.log(response)
                    selectedSet.clear();
                    saveSelectedSet(selectedSet);
                    location.reload();
                } else {
                    alert('Error reassigning leads. Please try again.');
                }
            });
        });
    }

    // Column personalization
    const COLUMNS_COOKIE = 'assigned_leads_columns';
    const allColumnKeys = Array.from(document.querySelectorAll('thead th[data-column]')).map(th => th.getAttribute('data-column'));
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
        document.querySelectorAll('thead th[data-column]').forEach(th => {
            const key = th.getAttribute('data-column');
            th.style.display = show.has(key) ? '' : 'none';
        });
        document.querySelectorAll('tbody tr').forEach(tr => {
            tr.querySelectorAll('[data-column]').forEach((td, index) => {
                const key = td.getAttribute('data-column');
                td.style.display = show.has(key) ? '' : 'none';
            });
        });
    }
    
    // Build and show columns modal
    const columnsBtn = document.getElementById('columnsBtn');
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

    // Ensure only one modal open at a time - prevents modal conflict
    document.addEventListener('show.bs.modal', function (event) {
        document.querySelectorAll('.modal.show').forEach(openModal => {
            if (openModal !== event.target) {
                const instance = bootstrap.Modal.getInstance(openModal);
                if (instance) {
                    instance.hide();
                }
            }
        });
    });

// View assignment history
    window.viewAssignmentHistory = function(leadId) {
    if (!assignmentHistoryModalInstance) {
        console.error('Assignment history modal not initialized');
        return;
    }
    
    const content = document.getElementById('assignmentHistoryContent');
    
    content.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>';
    assignmentHistoryModalInstance.show();
    
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
    };

// Reassign lead
    window.reassignLead = function(leadId) {
    if (!reassignModalInstance) {
        console.error('Reassign modal not initialized');
        return;
    }
    
    document.getElementById('reassignLeadId').value = leadId;
    reassignModalInstance.show();
    };

// Unassign lead
    window.unassignLead = function(leadId) {
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
    };

// Form submissions
    const reassignForm = document.getElementById('reassignForm');
    if (reassignForm) {
        reassignForm.addEventListener('submit', function(e) {
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
    }
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

.custom-field-container {
    border-left: 3px solid #0d6efd;
    padding-left: 15px;
    margin-bottom: 15px;
    background-color: #f8f9fa;
    border-radius: 0 5px 5px 0;
    padding: 15px;
}

.custom-field-container .form-label {
    color: #495057;
    font-weight: 600;
    margin-bottom: 8px;
}

.custom-field-container .form-control,
.custom-field-container .form-select {
    border: 1px solid #ced4da;
    border-radius: 4px;
}

.custom-field-container .form-control:focus,
.custom-field-container .form-select:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}
</style>

<?php include __DIR__ . '/../layout/footer.php'; ?>
