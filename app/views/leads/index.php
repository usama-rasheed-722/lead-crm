<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-users me-2"></i>Leads Management</h2>
    <div>
        <a href="index.php?action=assigned_leads" class="btn btn-outline-info me-2">
            <i class="fas fa-user-check me-2"></i>Assigned Leads
        </a>
        <a href="index.php?action=lead_add" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Add New Lead
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

<!-- Search and Filters -->
<div class="search-form">
    <form method="GET" action="index.php">
        <input type="hidden" name="action" value="leads">
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
                    <?php foreach ($leadSources as $source): ?>
                        <option value="<?= htmlspecialchars($source['id']) ?>" 
                                <?= ($filters['lead_source_id'] ?? '') == $source['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($source['name']) ?>
                        </option>
                    <?php endforeach; ?>
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
        </div>
        <div class="row mt-3">
            <div class="col-12">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-search me-2"></i>Search
                </button>
                <a href="index.php?action=leads" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-2"></i>Clear
                </a>
            </div>
        </div>
    </form>
</div>

<!-- Results -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Leads (<?= (int)($total ?? count($leads)) ?> found)</h5>
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
            <button type="button" class="btn btn-sm btn-info me-2" id="bulkAssignBtn" disabled>
                <i class="fas fa-user-plus me-1"></i>Assign Selected
            </button>
            <?php if (auth_user()['role'] === 'admin'): ?>
                <button type="button" class="btn btn-sm btn-danger me-2" id="bulkDeleteBtn" disabled>
                    <i class="fas fa-trash me-1"></i>Delete Selected
                </button>
            <?php endif; ?>
            <a href="index.php?action=export_csv&<?= http_build_query(array_merge($filters ?? [], ['search' => $search ?? ''])) ?>" class="btn btn-sm btn-outline-success me-2">
                <i class="fas fa-download me-1"></i>Export CSV
            </a>
            <a href="index.php?action=export_excel&<?= http_build_query(array_merge($filters ?? [], ['search' => $search ?? ''])) ?>" class="btn btn-sm btn-outline-success">
                <i class="fas fa-file-excel me-1"></i>Export Excel
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <?php if (!empty($leads)): ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>
                                <input type="checkbox" id="selectAll" class="form-check-input">
                            </th>
                            <th data-col-key="date">Date</th>
                            <th data-col-key="lead_id">Lead ID</th>
                            <th data-col-key="status">Status</th>
                            <th data-col-key="sdr">Lead Owner</th>
                            <th data-col-key="company">Company</th>
                            <th data-col-key="contact_name">Contact Name</th>
                            <th data-col-key="job_title">Job Title</th>
                            <th data-col-key="email">Email</th>
                            <th data-col-key="phone">Phone</th>
                            <th data-col-key="linkedin">LinkedIn</th>
                            <th data-col-key="website">Website</th>
                            <th data-col-key="industry">Industry</th>
                            <th data-col-key="lead_source">Lead Source</th>
                            <th data-col-key="tier">Tier</th>
                            <th data-col-key="lead_status">Lead Status</th>
                            <th data-col-key="clutch">Clutch Link</th>
                            <th data-col-key="insta">Insta</th>
                            <th data-col-key="social_profile">Social Profile</th>
                            <th data-col-key="address">Address</th>
                            <th data-col-key="description_information">Description Information</th>
                            <th data-col-key="whatsapp">Whatsapp</th>
                            <th data-col-key="next_step">Next Step</th>
                            <th data-col-key="other">Other</th>
                            <th data-col-key="country">Country</th>
                            <th data-col-key="lead_segment">Lead Segment</th>
                            <th data-col-key="sdr_name">SDR</th>
                            <th data-col-key="duplicate_status">Duplicate Status</th>
                            <th data-col-key="actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($leads as $lead): ?>
                            <tr>
                                <td>
                                    <input type="checkbox" class="form-check-input lead-checkbox" value="<?= $lead['id'] ?>">
                                </td>
                                <td data-col-key="date"><?= date('Y-m-d', strtotime($lead['created_at'])) ?></td>
                                <td data-col-key="lead_id">
                                    <a href="index.php?action=lead_view&id=<?= $lead['id'] ?>" class="text-decoration-none fw-bold">
                                        <?= htmlspecialchars($lead['lead_id']) ?>
                                    </a>
                                </td>
                                <td data-col-key="status">
                                    <span class="badge bg-secondary">
                                        <?= htmlspecialchars($lead['status_name'] ?: 'New Lead') ?>
                                    </span>
                                </td>
                                <td data-col-key="sdr"><?= htmlspecialchars($lead['sdr_name'] ?: 'N/A') ?></td>
                                <td data-col-key="company"><?= htmlspecialchars($lead['company'] ?: 'N/A') ?></td>
                                <td data-col-key="contact_name"><?= htmlspecialchars($lead['name'] ?: 'N/A') ?></td>
                                <td data-col-key="job_title"><?= htmlspecialchars($lead['job_title'] ?: 'N/A') ?></td>
                                <td data-col-key="email">
                                    <?php if ($lead['email']): ?>
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
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                                <td data-col-key="phone">
                                    <?php if ($lead['phone']): ?>
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
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                                <td data-col-key="linkedin">
                                    <?php if ($lead['linkedin']): ?>
                                        <div class="d-flex align-items-center gap-1">
                                            <a href="<?= ensure_url_protocol($lead['linkedin']) ?>" target="_blank" class="text-decoration-none">LinkedIn</a>
                                            <?php if ($lead['linkedin_verified'] ?? 0): ?>
                                                <span class="badge bg-success" title="Verified">
                                                    <i class="fas fa-check-circle"></i>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                                <td data-col-key="website">
                                    <?php if ($lead['website']): ?>
                                        <a href="<?= ensure_url_protocol($lead['website']) ?>" target="_blank" class="text-decoration-none">
                                            <i class="fas fa-external-link-alt me-1"></i>
                                            <?= htmlspecialchars(substr($lead['website'], 0, 30)) ?>
                                        </a>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                                <td data-col-key="industry"><?= htmlspecialchars($lead['industry'] ?: 'N/A') ?></td>
                                <td data-col-key="lead_source"><?= htmlspecialchars($lead['lead_source_name'] ?: 'N/A') ?></td>
                                <td data-col-key="tier"><?= htmlspecialchars($lead['tier'] ?: 'N/A') ?></td>
                                <td data-col-key="lead_status"><?= htmlspecialchars($lead['lead_status'] ?: 'N/A') ?></td>
                                <td data-col-key="clutch">
                                    <?php if ($lead['clutch']): ?>
                                        <a href="<?= ensure_url_protocol($lead['clutch']) ?>" target="_blank" class="text-decoration-none">Clutch</a>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                                <td data-col-key="insta">
                                    <?php if ($lead['insta']): ?>
                                        <a href="<?= ensure_url_protocol($lead['insta']) ?>" target="_blank" class="text-decoration-none">
                                            <i class="fab fa-instagram me-1"></i>Instagram
                                        </a>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                                <td data-col-key="social_profile">
                                    <?php if ($lead['social_profile']): ?>
                                        <a href="<?= ensure_url_protocol($lead['social_profile']) ?>" target="_blank" class="text-decoration-none">
                                            <i class="fas fa-link me-1"></i>View Profile
                                        </a>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                                <td data-col-key="address"><?= htmlspecialchars($lead['address'] ?: 'N/A') ?></td>
                                <td data-col-key="description_information"><?= htmlspecialchars($lead['description_information'] ?: 'N/A') ?></td>
                                <td data-col-key="whatsapp">
                                    <?php if ($lead['whatsapp']): ?>
                                        <div class="d-flex align-items-center gap-1">
                                            <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $lead['whatsapp']) ?>" target="_blank" class="text-decoration-none">
                                                <i class="fab fa-whatsapp me-1"></i><?= htmlspecialchars($lead['whatsapp']) ?>
                                            </a>
                                            <?php if ($lead['whatsapp_verified'] ?? 0): ?>
                                                <span class="badge bg-success" title="Verified">
                                                    <i class="fas fa-check-circle"></i>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                                <td data-col-key="next_step"><?= htmlspecialchars($lead['next_step'] ?: 'N/A') ?></td>
                                <td data-col-key="other"><?= htmlspecialchars($lead['other'] ?: 'N/A') ?></td>
                                <td data-col-key="country"><?= htmlspecialchars($lead['country'] ?: 'N/A') ?></td>
                                <td data-col-key="lead_segment"><?= htmlspecialchars($lead['lead_segment'] ?: 'N/A') ?></td>
                                <td data-col-key="sdr_name"><?= htmlspecialchars($lead['sdr_name'] ?: 'N/A') ?></td>
                                <td data-col-key="duplicate_status">
                                    <?php
                                    $status = $lead['duplicate_status'];
                                    if ($status === 'unique') {
                                        $statusClass = 'status-unique';
                                        $statusIcon = '✅';
                                    } elseif ($status === 'duplicate') {
                                        $statusClass = 'status-duplicate';
                                        $statusIcon = '🔁';
                                    } else {
                                        $statusClass = 'status-incomplete';
                                        $statusIcon = '⚠️';
                                    }
                                    ?>
                                    <span class="status-badge <?= $statusClass ?>">
                                        <?= $statusIcon ?> <?= ucfirst($lead['duplicate_status']) ?>
                                    </span>
                                </td>
                                <td data-col-key="actions">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="index.php?action=lead_view&id=<?= $lead['id'] ?>" 
                                           class="btn btn-outline-primary" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="index.php?action=lead_edit&id=<?= $lead['id'] ?>" 
                                           class="btn btn-outline-secondary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-info" 
                                                onclick="assignLead(<?= $lead['id'] ?>)" title="Assign Lead">
                                            <i class="fas fa-user-plus"></i>
                                        </button>
                                        <a href="index.php?action=lead_delete&id=<?= $lead['id'] ?>" 
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
            <?php if (!empty($totalPages) && $totalPages > 1): ?>
            <div class="p-3 d-flex justify-content-between align-items-center">
                <div class="text-muted small">
                    Showing <?= ($page - 1) * $limit + 1 ?> - <?= min($page * $limit, $total) ?> of <?= $total ?>
                </div>
                <nav>
                    <ul class="pagination mb-0">
                        <?php 
                        $buildQuery = function($p) use ($search, $filters) {
                            $params = array_merge(['action' => 'leads', 'page' => $p], $filters);
                            if (!empty($search)) { $params['search'] = $search; }
                            return 'index.php?' . http_build_query($params);
                        };
                        ?>
                        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= $buildQuery(max(1, $page - 1)) ?>" aria-label="Previous">&laquo;</a>
                        </li>
                        <?php 
                        $start = max(1, $page - 2);
                        $end = min($totalPages, $page + 2);
                        if ($start > 1) {
                            echo '<li class="page-item"><a class="page-link" href="' . $buildQuery(1) . '">1</a></li>';
                            if ($start > 2) echo '<li class="page-item disabled"><span class="page-link">…</span></li>';
                        }
                        for ($p = $start; $p <= $end; $p++) {
                            $active = $p == $page ? 'active' : '';
                            echo '<li class="page-item ' . $active . '"><a class="page-link" href="' . $buildQuery($p) . '">' . $p . '</a></li>';
                        }
                        if ($end < $totalPages) {
                            if ($end < $totalPages - 1) echo '<li class="page-item disabled"><span class="page-link">…</span></li>';
                            echo '<li class="page-item"><a class="page-link" href="' . $buildQuery($totalPages) . '">' . $totalPages . '</a></li>';
                        }
                        ?>
                        <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= $buildQuery(min($totalPages, $page + 1)) ?>" aria-label="Next">&raquo;</a>
                        </li>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No leads found</h5>
                <p class="text-muted">Try adjusting your search criteria or add a new lead.</p>
                <a href="index.php?action=lead_add" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Add New Lead
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

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
                        This will update the status for <span id="selectedCount">0</span> selected lead(s).
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
                <input type="hidden" name="lead_ids" id="bulkUpdateIds">
            </form>
        </div>
    </div>
</div>



<!-- Assignment Modals -->
<!-- Single Lead Assignment Modal -->
<div class="modal fade" id="assignModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user-plus me-2"></i>Assign Lead
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="assignForm" method="POST" action="index.php?action=assign_lead">
                <div class="modal-body">
                    <input type="hidden" id="assignLeadId" name="lead_id">
                    <div class="mb-3">
                        <label for="assignTo" class="form-label">Assign To</label>
                        <select class="form-select" id="assignTo" name="assigned_to" required>
                            <option value="">Select User</option>
                            <?php
                            // Get users for assignment dropdown
                            $userModel = new UserModel();
                            $users = $userModel->all();
                            foreach ($users as $user): 
                                if (in_array($user['role'], ['admin', 'sdr', 'manager'])):
                            ?>
                                <option value="<?= $user['id'] ?>">
                                    <?= htmlspecialchars($user['full_name'] ?: $user['username']) ?>
                                </option>
                            <?php 
                                endif;
                            endforeach; 
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="assignComment" class="form-label">Comment</label>
                        <textarea class="form-control" id="assignComment" name="comment" rows="3" 
                                  placeholder="Add a comment about this assignment..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-user-plus me-1"></i>Assign Lead
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Assignment Modal -->
<div class="modal fade" id="bulkAssignModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user-plus me-2"></i>Bulk Assign Leads
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="bulkAssignForm" data-action="index.php?action=bulk_assign_leads">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="bulkAssignTo" class="form-label">Assign To</label>
                        <select class="form-select" id="bulkAssignTo" name="assigned_to" required>
                            <option value="">Select User</option>
                            <?php
                            foreach ($users as $user): 
                                if (in_array($user['role'], ['admin', 'sdr', 'manager'])):
                            ?>
                                <option value="<?= $user['id'] ?>">
                                    <?= htmlspecialchars($user['full_name'] ?: $user['username']) ?>
                                </option>
                            <?php 
                                endif;
                            endforeach; 
                            ?>
                        </select>
                    </div>
                     <div class="mb-3">
                        <label for="reassignQuota" class="form-label">Quota</label>
                        <input type="checkbox" id="reassignQuota" name="reassign_quota" value="1">
                    </div>
            
                    <div class="mb-3">
                        <label for="bulkAssignDate" class="form-label">Date</label>
                        <input type="date" class="form-control" id="bulkAssignDate" name="date" disabled>
                    </div>
                    <div class="mb-3">
                        <label for="bulkAssignComment" class="form-label">Comment</label>
                        <textarea class="form-control" id="bulkAssignComment" name="comment" rows="3" 
                                  placeholder="Add a comment about this assignment..."></textarea>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <span id="bulkAssignCount">0</span> leads will be assigned.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-user-plus me-1"></i>Assign Leads
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Delete Form -->
<form id="bulkDeleteForm" method="POST" action="index.php?action=bulk_delete" style="display: none;">
    <input type="hidden" name="lead_ids" id="bulkDeleteIds">
</form>

<style>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const leadCheckboxes = document.querySelectorAll('.lead-checkbox');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const bulkUpdateBtn = document.getElementById('bulkUpdateBtn');
    const bulkAssignBtn = document.getElementById('bulkAssignBtn');
    const bulkDeleteForm = document.getElementById('bulkDeleteForm');
    const bulkDeleteIds = document.getElementById('bulkDeleteIds');
    const bulkUpdateModal = document.getElementById('bulkUpdateModal') ? new bootstrap.Modal(document.getElementById('bulkUpdateModal')) : null;
    const bulkUpdateIds = document.getElementById('bulkUpdateIds');
    const selectedCountSpan = document.getElementById('selectedCount');
    const clearSelectionBtn = document.getElementById('clearSelectionBtn');
    const selectionBadge = document.getElementById('selectionBadge');
    const selectionCount = document.getElementById('selectionCount');
    const columnsBtn = document.getElementById('columnsBtn');

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
    const SELECT_COOKIE = 'selected_lead_ids';
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
        if (bulkDeleteBtn) bulkDeleteBtn.disabled = count === 0;
        if (bulkUpdateBtn) bulkUpdateBtn.disabled = count === 0;
        if (bulkAssignBtn) bulkAssignBtn.disabled = count === 0;
        if (clearSelectionBtn) clearSelectionBtn.disabled = count === 0;
        if (selectedCountSpan) selectedCountSpan.textContent = count;
        if (selectionCount) selectionCount.textContent = count;
        if (selectionBadge) {
            selectionBadge.style.display = count > 0 ? 'inline-block' : 'none';
        }
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

    // Bulk delete functionality
    if (bulkDeleteBtn) {
        bulkDeleteBtn.addEventListener('click', function() {
            const ids = Array.from(selectedSet);
            if (ids.length === 0) return;

            if (confirm(`Are you sure you want to delete ${ids.length} selected lead(s)? This action cannot be undone.`)) {
                bulkDeleteIds.value = ids.join(',');
                bulkDeleteForm.submit();
            }
        });
    }

    // Delete confirmation for individual leads
    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to delete this lead? This action cannot be undone.')) {
                e.preventDefault();
            }
        });
    });

    // Bulk update functionality
    if (bulkUpdateBtn && bulkUpdateModal) {
        bulkUpdateBtn.addEventListener('click', function() {
            const ids = Array.from(selectedSet);
            if (ids.length === 0) return;
            bulkUpdateIds.value = ids.join(',');
            bulkUpdateModal.show();
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

    // Column personalization
    const COLUMNS_COOKIE = 'leads_columns';
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
        // Toggle header
        document.querySelectorAll('thead th[data-col-key]').forEach(th => {
            const key = th.getAttribute('data-col-key');
            th.style.display = show.has(key) ? '' : 'none';
        });
        // Toggle body cells
        document.querySelectorAll('tbody tr').forEach(tr => {
            tr.querySelectorAll('[data-col-key]').forEach(td => {
                const key = td.getAttribute('data-col-key');
                td.style.display = show.has(key) ? '' : 'none';
            });
        });
    }
    // Build and show columns modal
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
            const current = getColumnsSelection() || allColumnKeys; // default show all
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
    
    // Function to create custom field HTML (shared with lead view)
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
    // Assignment functionality
    function assignLead(leadId) {
        document.getElementById('assignLeadId').value = leadId;
        const modal = new bootstrap.Modal(document.getElementById('assignModal'));
        modal.show();
    }

    function bulkAssign() {
        const selectedLeads = Array.from(document.querySelectorAll('.lead-checkbox:checked')).map(cb => cb.value);
        if (selectedLeads.length === 0) {
            alert('Please select leads to assign.');
            return;
        }
      
        // Check if the element exists before setting textContent
        const bulkAssignCountElement = document.getElementById('bulkAssignCount');
        if (bulkAssignCountElement) {
            bulkAssignCountElement.textContent = selectedLeads.length;
        } else {
            console.warn('bulkAssignCount element not found, trying to find it after modal is shown');
        }
        
        // Check if the modal exists before showing it
        const bulkAssignModalElement = document.getElementById('bulkAssignModal');
        if (bulkAssignModalElement) {
            const modal = new bootstrap.Modal(bulkAssignModalElement);
            
            // Add event listener to update count after modal is shown
            bulkAssignModalElement.addEventListener('shown.bs.modal', function() {
                const countElement = document.getElementById('bulkAssignCount');
                if (countElement) {
                    countElement.textContent = selectedLeads.length;
                }
            }, { once: true }); // Use once: true to prevent multiple listeners
            
            modal.show();
        } else {
            console.error('Bulk assign modal not found in DOM');
        }
        
    }

    document.getElementById('reassignQuota').addEventListener('change', function() {
        const dateInput = document.getElementById('bulkAssignDate');
        if (!this.checked) {
              dateInput.value = '';
            dateInput.disabled = true;
        } else {
            dateInput.value = new Date().toISOString().split('T')[0];
            dateInput.disabled = false;
        }
    });

    // Add event listener for bulk assign button
    if (bulkAssignBtn) {
        bulkAssignBtn.addEventListener('click', bulkAssign);
    }

    // Form submissions
    const assignForm = document.getElementById('assignForm');
    if (assignForm) {
        assignForm.addEventListener('submit', function(e) {
            // Debug: Log form data before submission
            const formData = new FormData(this);
            console.log('Individual assignment form data:');
            for (let [key, value] of formData.entries()) {
                console.log(key, value);
            }
            
            // Add redirect URL to form data
            const redirectInput = document.createElement('input');
            redirectInput.type = 'hidden';
            redirectInput.name = 'redirect_url';
            redirectInput.value = window.location.href;
            this.appendChild(redirectInput);
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.textContent = 'Assigning...';
            submitBtn.disabled = true;
            
            // Let the form submit naturally to the action URL
            // The controller will handle the redirect back to this page
        });
    }

    const bulkAssignForm = document.getElementById('bulkAssignForm');
    if (bulkAssignForm) {
        bulkAssignForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const selectedLeads = Array.from(document.querySelectorAll('.lead-checkbox:checked')).map(cb => cb.value);
            if (!selectedLeads.length) {
                alert('Please select leads to assign.');
                return;
            }

            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn ? submitBtn.textContent : '';
            if (submitBtn) {
                submitBtn.textContent = 'Assigning...';
                submitBtn.disabled = true;
            }

            const formData = new FormData(this);
            selectedLeads.forEach(id => formData.append('lead_ids[]', id));
            formData.set('reassign_quota', document.getElementById('reassignQuota').checked ? 1 : 0);
            formData.set('date', document.getElementById('bulkAssignDate').value || '');
            formData.set('comment', document.getElementById('bulkAssignComment').value || '');
            formData.set('redirect_url', window.location.href);

            try {
                const response = await fetch(this.dataset.action || 'index.php?action=bulk_assign_leads', {
                    method: 'POST',
                    body: formData
                });

                if (!response.ok) {
                    throw new Error('Error assigning leads. Please try again.');
                }

                // Attempt to read JSON response for additional messaging (optional)
                let message = '';
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    const data = await response.json();
                    if (data?.error) {
                        throw new Error(data.error);
                    }
                    message = data?.message || '';
                }

                selectedSet.clear();
                saveSelectedSet(selectedSet);

                const bulkAssignModalElement = document.getElementById('bulkAssignModal');
                if (bulkAssignModalElement) {
                    const modalInstance = bootstrap.Modal.getInstance(bulkAssignModalElement) || new bootstrap.Modal(bulkAssignModalElement);
                    modalInstance.hide();
                }

                if (message) {
                    alert(message);
                }

                location.reload();
            } catch (error) {
                console.error('Bulk assignment failed:', error);
                alert(error.message || 'Error assigning leads. Please try again.');
            } finally {
                if (submitBtn) {
                    submitBtn.textContent = originalText || 'Assign Leads';
                    submitBtn.disabled = false;
                }
            }
        });
    }
});
</script>



<?php include __DIR__ . '/../layout/footer.php'; ?>