<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-users me-2"></i>Leads Management</h2>
    <div>
        <a href="index.php?action=lead_add" class="btn btn-primary me-2">
            <i class="fas fa-plus me-2"></i>Add New Lead
        </a>
        <a href="index.php?action=leads" class="btn btn-outline-secondary">
            <i class="fas fa-list me-2"></i>Full Leads List
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

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="index.php">
            <input type="hidden" name="action" value="leads_management">
            <div class="row g-3">
                <div class="col-md-4">
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
                <div class="col-md-4">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All Statuses</option>
                        <?php foreach ($statuses as $status): ?>
                            <option value="<?= htmlspecialchars($status['name']) ?>" <?= ($filters['status'] ?? '') === $status['name'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($status['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search me-2"></i>Filter
                    </button>
                    <a href="index.php?action=leads_management" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-2"></i>Clear
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Results -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Leads (<?= (int)($total ?? count($leads)) ?> found)</h5>
        <div>
            <button type="button" class="btn btn-sm btn-primary me-2" id="bulkUpdateBtn" disabled>
                <i class="fas fa-edit me-1"></i>Bulk Update Status
            </button>
            <a href="index.php?action=export_csv&<?= http_build_query(array_merge($filters ?? [], ['search' => ''])) ?>" class="btn btn-sm btn-outline-success">
                <i class="fas fa-download me-1"></i>Export CSV
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
                            <th>Lead ID</th>
                            <th>Company Name</th>
                            <th>Contact Name</th>
                            <th>Website</th>
                            <th>LinkedIn</th>
                            <th>Clutch ID</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($leads as $lead): ?>
                            <tr>
                                <td>
                                    <input type="checkbox" class="form-check-input lead-checkbox" value="<?= $lead['id'] ?>">
                                </td>
                                <td>
                                    <a href="index.php?action=lead_view&id=<?= $lead['id'] ?>" class="text-decoration-none fw-bold">
                                        <?= htmlspecialchars($lead['lead_id']) ?>
                                    </a>
                                </td>
                                <td><?= htmlspecialchars($lead['company'] ?: 'N/A') ?></td>
                                <td><?= htmlspecialchars($lead['contact_name'] ?: 'N/A') ?></td>
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
                                <td>
                                    <?php if ($lead['linkedin']): ?>
                                        <a href="<?= htmlspecialchars($lead['linkedin']) ?>" target="_blank" class="text-decoration-none">LinkedIn</a>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($lead['clutch']): ?>
                                        <a href="<?= htmlspecialchars($lead['clutch']) ?>" target="_blank" class="text-decoration-none">Clutch</a>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">
                                        <?= htmlspecialchars($lead['status'] ?: 'New Lead') ?>
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
                        $buildQuery = function($p) use ($filters) {
                            $params = array_merge(['action' => 'leads_management', 'page' => $p], $filters);
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
                <p class="text-muted">Try adjusting your filter criteria or add a new lead.</p>
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
                        <label for="new_status" class="form-label">New Status</label>
                        <select class="form-select" id="new_status" name="new_status" required>
                            <option value="">Select Status</option>
                            <?php foreach ($statuses as $status): ?>
                                <option value="<?= htmlspecialchars($status['name']) ?>">
                                    <?= htmlspecialchars($status['name']) ?>
                                </option>
                            <?php endforeach; ?>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const leadCheckboxes = document.querySelectorAll('.lead-checkbox');
    const bulkUpdateBtn = document.getElementById('bulkUpdateBtn');
    const bulkUpdateModal = new bootstrap.Modal(document.getElementById('bulkUpdateModal'));
    const bulkUpdateForm = document.getElementById('bulkUpdateForm');
    const bulkUpdateIds = document.getElementById('bulkUpdateIds');
    const selectedCountSpan = document.getElementById('selectedCount');

    // Select all functionality
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            leadCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkUpdateButton();
        });
    }

    // Individual checkbox change
    leadCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateBulkUpdateButton();
            updateSelectAllState();
        });
    });

    function updateBulkUpdateButton() {
        const checkedBoxes = document.querySelectorAll('.lead-checkbox:checked');
        if (bulkUpdateBtn) {
            bulkUpdateBtn.disabled = checkedBoxes.length === 0;
        }
        if (selectedCountSpan) {
            selectedCountSpan.textContent = checkedBoxes.length;
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

    // Bulk update functionality
    if (bulkUpdateBtn) {
        bulkUpdateBtn.addEventListener('click', function() {
            const checkedBoxes = document.querySelectorAll('.lead-checkbox:checked');
            if (checkedBoxes.length === 0) return;

            const ids = Array.from(checkedBoxes).map(cb => cb.value);
            bulkUpdateIds.value = ids.join(',');
            bulkUpdateModal.show();
        });
    }

    // Form submission
    if (bulkUpdateForm) {
        bulkUpdateForm.addEventListener('submit', function(e) {
            const newStatus = document.getElementById('new_status').value;
            if (!newStatus) {
                e.preventDefault();
                alert('Please select a status');
                return;
            }
            
            const checkedBoxes = document.querySelectorAll('.lead-checkbox:checked');
            if (!confirm(`Are you sure you want to update the status to "${newStatus}" for ${checkedBoxes.length} selected lead(s)?`)) {
                e.preventDefault();
            }
        });
    }
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
