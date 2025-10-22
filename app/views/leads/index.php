<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-users me-2"></i>Leads Management</h2>
    <a href="index.php?action=lead_add" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Add New Lead
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
            <button type="button" class="btn btn-sm btn-outline-secondary me-2" id="columnsBtn">
                <i class="fas fa-columns me-1"></i>Columns
            </button>
            <button type="button" class="btn btn-sm btn-primary me-2" id="bulkUpdateBtn" disabled>
                <i class="fas fa-edit me-1"></i>Bulk Update Status
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
                                <td data-col-key="linkedin">
                                    <?php if ($lead['linkedin']): ?>
                                        <a href="<?=  $lead['linkedin'] ?>" target="_blank" class="text-decoration-none">LinkedIn</a>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                                <td data-col-key="website">
                                    <?php if ($lead['website']): ?>
                                        <a href="<?= $lead['website'] ?>" target="_blank" class="text-decoration-none">
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
                                        <a href="<?= htmlspecialchars($lead['clutch']) ?>" target="_blank" class="text-decoration-none">Clutch</a>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                                <td data-col-key="insta"><?= htmlspecialchars($lead['insta'] ?: 'N/A') ?></td>
                                <td data-col-key="social_profile"><?= htmlspecialchars($lead['social_profile'] ?: 'N/A') ?></td>
                                <td data-col-key="address"><?= htmlspecialchars($lead['address'] ?: 'N/A') ?></td>
                                <td data-col-key="description_information"><?= htmlspecialchars($lead['description_information'] ?: 'N/A') ?></td>
                                <td data-col-key="whatsapp"><?= htmlspecialchars($lead['whatsapp'] ?: 'N/A') ?></td>
                                <td data-col-key="next_step"><?= htmlspecialchars($lead['next_step'] ?: 'N/A') ?></td>
                                <td data-col-key="other"><?= htmlspecialchars($lead['other'] ?: 'N/A') ?></td>
                                <td data-col-key="country"><?= htmlspecialchars($lead['country'] ?: 'N/A') ?></td>
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
                                <option value="<?= $st['id'] ?>">
                                    <?= htmlspecialchars($st['name']) ?>
                                </option>
                            <?php endforeach; endif; ?>
                        </select>
                    </div>
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

<!-- Bulk Delete Form -->
<form id="bulkDeleteForm" method="POST" action="index.php?action=bulk_delete" style="display: none;">
    <input type="hidden" name="lead_ids" id="bulkDeleteIds">
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const leadCheckboxes = document.querySelectorAll('.lead-checkbox');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const bulkUpdateBtn = document.getElementById('bulkUpdateBtn');
    const bulkDeleteForm = document.getElementById('bulkDeleteForm');
    const bulkDeleteIds = document.getElementById('bulkDeleteIds');
    const bulkUpdateModal = document.getElementById('bulkUpdateModal') ? new bootstrap.Modal(document.getElementById('bulkUpdateModal')) : null;
    const bulkUpdateIds = document.getElementById('bulkUpdateIds');
    const selectedCountSpan = document.getElementById('selectedCount');
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
        if (selectedCountSpan) selectedCountSpan.textContent = count;
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
    const bulkUpdateForm = document.getElementById('bulkUpdateForm');
    if (bulkUpdateForm) {
        bulkUpdateForm.addEventListener('submit', function(e) {
            const newStatusId = document.getElementById('new_status_id').value;
            const newStatusName = document.getElementById('new_status_id').selectedOptions[0]?.text || '';
            const count = selectedSet.size;
            if (!newStatusId || count === 0) {
                e.preventDefault();
                alert('Select leads and a status');
                return;
            }
            if (!confirm(`Are you sure you want to update the status to "${newStatusName}" for ${count} selected lead(s)?`)) {
                e.preventDefault();
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
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>