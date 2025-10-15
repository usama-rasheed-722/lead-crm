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
            <div class="col-md-4">
                <label for="search" class="form-label">Search</label>
                <input type="text" class="form-control" id="search" name="search" 
                       value="<?= htmlspecialchars($search ?? '') ?>" 
                       placeholder="Search by name, company, email, website...">
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
                <label for="duplicate_status" class="form-label">Status</label>
                <select class="form-select" id="duplicate_status" name="duplicate_status">
                    <option value="">All Status</option>
                    <option value="unique" <?= ($filters['duplicate_status'] ?? '') === 'unique' ? 'selected' : '' ?>>✅ Unique</option>
                    <option value="duplicate" <?= ($filters['duplicate_status'] ?? '') === 'duplicate' ? 'selected' : '' ?>>🔁 Duplicate</option>
                    <option value="incomplete" <?= ($filters['duplicate_status'] ?? '') === 'incomplete' ? 'selected' : '' ?>>⚠️ Incomplete</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="lead_source" class="form-label">Lead Source</label>
                <select class="form-select" id="lead_source" name="lead_source">
                    <option value="">All Sources</option>
                    <?php
                    $sources = ['linkedin' => 'LinkedIn', 'clutch' => 'Clutch', 'gmb' => 'GMB'];
                    foreach ($sources as $key => $label): ?>
                        <option value="<?= $key ?>" <?= ($filters['lead_source'] ?? '') === $key ? 'selected' : '' ?>><?= $label ?></option>
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
                            <?php if (auth_user()['role'] === 'admin'): ?>
                            <th>
                                <input type="checkbox" id="selectAll" class="form-check-input">
                            </th>
                            <?php endif; ?>
                            <th>Date</th>
                            <th>Lead ID</th>
                            <th>Lead Owner</th>
                            <th>Company</th>
                            <th>Contact Name</th>
                            <th>Job Title</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>LinkedIn</th>
                            <th>Website</th>
                            <th>Industry</th>
                            <th>Lead Source</th>
                            <th>Tier</th>
                            <th>Lead Status</th>
                            <th>Clutch Link</th>
                            <th>Insta</th>
                            <th>Social Profile</th>
                            <th>Address</th>
                            <th>Description Information</th>
                            <th>Whatsapp</th>
                            <th>Next Step</th>
                            <th>Other</th>
                            <th>Status</th>
                            <th>Country</th>
                            <th>SDR</th>
                            <th>Duplicate Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($leads as $lead): ?>
                            <tr>
                                <?php if (auth_user()['role'] === 'admin'): ?>
                                <td>
                                    <input type="checkbox" class="form-check-input lead-checkbox" value="<?= $lead['id'] ?>">
                                </td>
                                <?php endif; ?>
                                <td><?= date('Y-m-d', strtotime($lead['created_at'])) ?></td>
                                <td>
                                    <a href="index.php?action=lead_view&id=<?= $lead['id'] ?>" class="text-decoration-none fw-bold">
                                        <?= htmlspecialchars($lead['lead_id']) ?>
                                    </a>
                                </td>
                                <td><?= htmlspecialchars($lead['sdr_name'] ?: 'N/A') ?></td>
                                <td><?= htmlspecialchars($lead['company'] ?: 'N/A') ?></td>
                                <td><?= htmlspecialchars($lead['name'] ?: 'N/A') ?></td>
                                <td><?= htmlspecialchars($lead['job_title'] ?: 'N/A') ?></td>
                                <td>
                                    <?php if ($lead['email']): ?>
                                        <a href="mailto:<?= htmlspecialchars($lead['email']) ?>" class="text-decoration-none">
                                            <?= htmlspecialchars($lead['email']) ?>
                                        </a>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($lead['phone']): ?>
                                        <a href="tel:<?= htmlspecialchars($lead['phone']) ?>" class="text-decoration-none">
                                            <?= htmlspecialchars($lead['phone']) ?>
                                        </a>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($lead['linkedin']): ?>
                                        <a href="<?= htmlspecialchars($lead['linkedin']) ?>" target="_blank" class="text-decoration-none">LinkedIn</a>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($lead['website']): ?>
                                        <a href="<?= htmlspecialchars($lead['website']) ?>" target="_blank" class="text-decoration-none">
                                            <i class="fas fa-external-link-alt me-1"></i>
                                            <?= htmlspecialchars(substr($lead['website'], 0, 30)) ?>
                                        </a>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($lead['industry'] ?: 'N/A') ?></td>
                                <td><?= htmlspecialchars($lead['lead_source'] ?: 'N/A') ?></td>
                                <td><?= htmlspecialchars($lead['tier'] ?: 'N/A') ?></td>
                                <td><?= htmlspecialchars($lead['lead_status'] ?: 'N/A') ?></td>
                                <td>
                                    <?php if ($lead['clutch']): ?>
                                        <a href="<?= htmlspecialchars($lead['clutch']) ?>" target="_blank" class="text-decoration-none">Clutch</a>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($lead['insta'] ?: 'N/A') ?></td>
                                <td><?= htmlspecialchars($lead['social_profile'] ?: 'N/A') ?></td>
                                <td><?= htmlspecialchars($lead['address'] ?: 'N/A') ?></td>
                                <td><?= htmlspecialchars($lead['description_information'] ?: 'N/A') ?></td>
                                <td><?= htmlspecialchars($lead['whatsapp'] ?: 'N/A') ?></td>
                                <td><?= htmlspecialchars($lead['next_step'] ?: 'N/A') ?></td>
                                <td><?= htmlspecialchars($lead['other'] ?: 'N/A') ?></td>
                                <td><?= htmlspecialchars($lead['status'] ?: 'N/A') ?></td>
                                <td><?= htmlspecialchars($lead['country'] ?: 'N/A') ?></td>
                                <td><?= htmlspecialchars($lead['sdr_name'] ?: 'N/A') ?></td>
                                <td>
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
                                <td>
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

<!-- Bulk Delete Form -->
<form id="bulkDeleteForm" method="POST" action="index.php?action=bulk_delete" style="display: none;">
    <input type="hidden" name="lead_ids" id="bulkDeleteIds">
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const leadCheckboxes = document.querySelectorAll('.lead-checkbox');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const bulkDeleteForm = document.getElementById('bulkDeleteForm');
    const bulkDeleteIds = document.getElementById('bulkDeleteIds');

    // Select all functionality
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            leadCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkDeleteButton();
        });
    }

    // Individual checkbox change
    leadCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateBulkDeleteButton();
            updateSelectAllState();
        });
    });

    function updateBulkDeleteButton() {
        const checkedBoxes = document.querySelectorAll('.lead-checkbox:checked');
        if (bulkDeleteBtn) {
            bulkDeleteBtn.disabled = checkedBoxes.length === 0;
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

    // Bulk delete functionality
    if (bulkDeleteBtn) {
        bulkDeleteBtn.addEventListener('click', function() {
            const checkedBoxes = document.querySelectorAll('.lead-checkbox:checked');
            if (checkedBoxes.length === 0) return;

            if (confirm(`Are you sure you want to delete ${checkedBoxes.length} selected lead(s)? This action cannot be undone.`)) {
                const ids = Array.from(checkedBoxes).map(cb => cb.value);
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
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>