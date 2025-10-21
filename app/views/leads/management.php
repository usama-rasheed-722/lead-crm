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
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="bulkUpdateModalLabel">
                    <i class="fas fa-users me-2"></i>Bulk Update Status
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="bulkUpdateForm" method="POST" action="index.php?action=bulk_update_status_with_custom_fields">
                <div class="modal-body">
                    <!-- Selected Leads Summary -->
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <div class="card border-primary">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="fas fa-list me-2"></i>Selected Leads Summary
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-primary mb-3">
                                        <i class="fas fa-info-circle me-2"></i>
                                        You are about to update the status for <strong><span id="selectedCount">0</span> selected lead(s)</strong>.
                                    </div>
                                    <div id="selectedLeadsList" class="small text-muted">
                                        <!-- Selected leads will be listed here -->
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-success">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="fas fa-cog me-2"></i>Update Settings
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="sendNotification" name="send_notification" checked>
                                        <label class="form-check-label" for="sendNotification">
                                            Send notification email
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="addNote" name="add_note">
                                        <label class="form-check-label" for="addNote">
                                            Add note to leads
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Status Selection -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="fas fa-flag me-2"></i>Status Selection
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="new_status" class="form-label fw-bold">New Status</label>
                                        <select class="form-select form-select-lg" id="new_status" name="new_status" required>
                                            <option value="">Select Status</option>
                                            <?php 
                                            $statusModel = new StatusModel();
                                            foreach ($statuses as $status): ?>
                                                <?php if (!$status['restrict_bulk_update']): 
                                                    $customFields = $statusModel->getCustomFieldsByName($status['name']);
                                                    $hasFields = count($customFields) > 0;
                                                ?>
                                                    <option value="<?= htmlspecialchars($status['name']) ?>" data-has-fields="<?= $hasFields ? 'true' : 'false' ?>">
                                                        <?= htmlspecialchars($status['name']) ?><?= $hasFields ? ' 📝' : '' ?>
                                                    </option>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="form-text">
                                            <i class="fas fa-info-circle text-info me-1"></i>
                                            Only statuses that allow bulk updates are shown. 
                                            <span class="text-muted">📝 indicates statuses that require additional information</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="fas fa-info-circle me-2"></i>Status Information
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div id="statusInfo" class="text-muted">
                                        <p class="mb-2">
                                            <i class="fas fa-arrow-right me-2"></i>
                                            Select a status to see additional information and required fields.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Dynamic Custom Fields Container -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <div id="bulkCustomFieldsContainer"></div>
                        </div>
                    </div>

                    <!-- Additional Options -->
                    <div class="row mt-3" id="additionalOptions" style="display: none;">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="fas fa-plus me-2"></i>Additional Options
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="bulkNote" class="form-label">Note (Optional)</label>
                                                <textarea class="form-control" id="bulkNote" name="bulk_note" rows="3" placeholder="Add a note to all selected leads..."></textarea>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="bulkTags" class="form-label">Tags (Optional)</label>
                                                <input type="text" class="form-control" id="bulkTags" name="bulk_tags" placeholder="Enter tags separated by commas...">
                                                <div class="form-text">Tags will be added to all selected leads</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-sync me-2"></i>Update Status for <span id="submitCount">0</span> Lead(s)
                    </button>
                </div>
                <input type="hidden" name="lead_ids" id="bulkUpdateIds">
            </form>
        </div>
    </div>
</div>

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
    margin-bottom: 8px;
}

.custom-field-container .form-control,
.custom-field-container .form-select {
    border: 1px solid #ced4da;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.custom-field-container .form-control:focus,
.custom-field-container .form-select:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

.status-with-fields {
    font-weight: 600;
}

.loading-custom-fields {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: loading 1.5s infinite;
}

@keyframes loading {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}

.modal-xl {
    max-width: 1200px;
}

.bulk-modal .card {
    border: 1px solid #dee2e6;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.bulk-modal .card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.bulk-modal .form-select-lg {
    padding: 0.75rem 1rem;
    font-size: 1.1rem;
}

.bulk-modal .alert-sm {
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
}

.selected-lead-item {
    transition: background-color 0.2s ease;
}

.selected-lead-item:hover {
    background-color: #f8f9fa;
}
</style>

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
            
            // Update selected leads list
            updateSelectedLeadsList(checkedBoxes);
            
            // Update submit button count
            document.getElementById('submitCount').textContent = checkedBoxes.length;
            
            bulkUpdateModal.show();
        });
    }
    
    function updateSelectedLeadsList(checkedBoxes) {
        const selectedLeadsList = document.getElementById('selectedLeadsList');
        let leadsList = '<div class="row">';
        
        checkedBoxes.forEach((checkbox, index) => {
            const row = checkbox.closest('tr');
            const leadId = row.querySelector('td:nth-child(2) a').textContent;
            const company = row.querySelector('td:nth-child(3)').textContent;
            const contact = row.querySelector('td:nth-child(4)').textContent;
            
            leadsList += `
                <div class="col-md-6 mb-2">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-user-circle me-2 text-primary"></i>
                        <div>
                            <div class="fw-bold">${leadId}</div>
                            <small class="text-muted">${company} - ${contact}</small>
                        </div>
                    </div>
                </div>
            `;
        });
        
        leadsList += '</div>';
        selectedLeadsList.innerHTML = leadsList;
    }

    // Status change handler for custom fields
    const newStatusSelect = document.getElementById('new_status');
    const bulkCustomFieldsContainer = document.getElementById('bulkCustomFieldsContainer');
    
    if (newStatusSelect) {
        newStatusSelect.addEventListener('change', function() {
            const selectedStatus = this.value;
            const selectedOption = this.options[this.selectedIndex];
            const hasFields = selectedOption.dataset.hasFields === 'true';
            
            // Clear previous custom fields
            bulkCustomFieldsContainer.innerHTML = '';
            
            // Update status information
            updateStatusInfo(selectedStatus, hasFields);
            
            if (selectedStatus) {
                // Show loading indicator
                bulkCustomFieldsContainer.innerHTML = '<div class="text-center py-3"><i class="fas fa-spinner fa-spin me-2"></i>Loading custom fields...</div>';
                
                // Fetch custom fields for the selected status
                fetch(`index.php?action=get_custom_fields_for_status&status=${encodeURIComponent(selectedStatus)}`)
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
                        
                        // Show additional options
                        document.getElementById('additionalOptions').style.display = 'block';
                    })
                    .catch(error => {
                        console.error('Error fetching custom fields:', error);
                        bulkCustomFieldsContainer.innerHTML = '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i>Error loading custom fields. Please try again.</div>';
                    });
            } else {
                // Hide additional options when no status selected
                document.getElementById('additionalOptions').style.display = 'none';
            }
        });
    }
    
    function updateStatusInfo(statusName, hasFields) {
        const statusInfo = document.getElementById('statusInfo');
        
        if (statusName) {
            let infoHtml = `
                <div class="mb-3">
                    <h6 class="text-primary">${statusName}</h6>
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-${hasFields ? 'exclamation-triangle' : 'check-circle'} me-2 text-${hasFields ? 'warning' : 'success'}"></i>
                        <span class="small">${hasFields ? 'Requires additional information' : 'No additional fields required'}</span>
                    </div>
            `;
            
            if (hasFields) {
                infoHtml += `
                    <div class="alert alert-warning alert-sm mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        <small>This status has custom fields that must be filled out for all selected leads.</small>
                    </div>
                `;
            } else {
                infoHtml += `
                    <div class="alert alert-success alert-sm mb-0">
                        <i class="fas fa-check-circle me-2"></i>
                        <small>This status can be applied without additional information.</small>
                    </div>
                `;
            }
            
            infoHtml += '</div>';
            statusInfo.innerHTML = infoHtml;
        } else {
            statusInfo.innerHTML = `
                <p class="mb-2">
                    <i class="fas fa-arrow-right me-2"></i>
                    Select a status to see additional information and required fields.
                </p>
            `;
        }
    }
    
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

    // Form submission
    if (bulkUpdateForm) {
        bulkUpdateForm.addEventListener('submit', function(e) {
            const newStatus = document.getElementById('new_status').value;
            if (!newStatus) {
                e.preventDefault();
                alert('Please select a status');
                return;
            }
            
            // Validate required custom fields
            const requiredFields = bulkCustomFieldsContainer.querySelectorAll('[required]');
            let missingFields = [];
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    const label = field.previousElementSibling;
                    if (label && label.tagName === 'LABEL') {
                        missingFields.push(label.textContent.replace('*', '').trim());
                    }
                }
            });
            
            if (missingFields.length > 0) {
                e.preventDefault();
                alert('Please fill in the following required fields:\n' + missingFields.join('\n'));
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
