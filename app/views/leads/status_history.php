<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>
        <i class="fas fa-history me-2"></i>Status History - <?= htmlspecialchars($lead['lead_id']) ?>
    </h2>
    <div>
        <a href="index.php?action=lead_view&id=<?= $lead['id'] ?>" class="btn btn-outline-primary me-2">
            <i class="fas fa-eye me-2"></i>View Lead
        </a>
        <a href="index.php?action=leads" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Leads
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

<!-- Lead Information Summary -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Lead Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Lead ID:</strong> <?= htmlspecialchars($lead['lead_id']) ?></p>
                        <p><strong>Company:</strong> <?= htmlspecialchars($lead['company'] ?: 'N/A') ?></p>
                        <p><strong>Contact Name:</strong> <?= htmlspecialchars($lead['contact_name'] ?: 'N/A') ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Email:</strong> <?= htmlspecialchars($lead['email'] ?: 'N/A') ?></p>
                        <p><strong>Phone:</strong> <?= htmlspecialchars($lead['phone'] ?: 'N/A') ?></p>
                        <p><strong>Current Status:</strong> 
                            <span class="badge bg-primary"><?= htmlspecialchars($lead['status_name'] ?: 'New Lead') ?></span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#quickStatusChangeModal">
                        <i class="fas fa-sync me-2"></i>Change Status
                    </button>
                    <a href="index.php?action=lead_edit&id=<?= $lead['id'] ?>" class="btn btn-outline-primary">
                        <i class="fas fa-edit me-2"></i>Edit Lead
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Status History -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Status Change History</h5>
        <span class="badge bg-secondary"><?= count($statusHistory) ?> changes</span>
    </div>
    <div class="card-body p-0">
        <?php if (!empty($statusHistory)): ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date & Time</th>
                            <th>Previous Status</th>
                            <th>New Status</th>
                            <th>Changed By</th>
                            <th>Custom Fields</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($statusHistory as $h): ?>
                            <tr>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="fw-bold"><?= date('M j, Y', strtotime($h['changed_at'])) ?></span>
                                        <small class="text-muted"><?= date('g:i A', strtotime($h['changed_at'])) ?></small>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($h['old_status']): ?>
                                        <span class="badge bg-light text-dark"><?= htmlspecialchars($h['old_status']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-primary"><?= htmlspecialchars($h['new_status']) ?></span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-user-circle me-2 text-muted"></i>
                                        <span><?= htmlspecialchars($h['full_name'] ?: $h['username'] ?: 'System') ?></span>
                                    </div>
                                </td>
                                <td>
                                    <?php if (!empty($h['custom_fields_data'])): ?>
                                        <?php 
                                        $customData = json_decode($h['custom_fields_data'], true);
                                        if ($customData && count($customData) > 0):
                                        ?>
                                            <button class="btn btn-sm btn-outline-info" type="button" data-bs-toggle="collapse" data-bs-target="#customFields_<?= $h['id'] ?>">
                                                <i class="fas fa-info-circle me-1"></i>
                                                View Fields (<?= count($customData) ?>)
                                            </button>
                                        <?php else: ?>
                                            <span class="text-muted">None</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">None</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button type="button" class="btn btn-outline-secondary" title="View Details" onclick="showHistoryDetails(<?= $h['id'] ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php if (!empty($h['custom_fields_data'])): ?>
                                <?php 
                                $customData = json_decode($h['custom_fields_data'], true);
                                // pr($h ,1);
                                if ($customData && count($customData) > 0):
                                ?>
                                    <tr class="collapse" id="customFields_<?= $h['id'] ?>">
                                        <td colspan="6">
                                            <div class="card card-body bg-light">
                                                <h6 class="card-title mb-3">
                                                    <i class="fas fa-list me-2"></i>Custom Fields Data
                                                </h6>
                                                <div class="row">
                                                    <?php foreach ($customData as $fieldName => $fieldValue): ?>
                                                        <div class="col-md-6 mb-2">
                                                            <div class="d-flex">
                                                                <strong class="me-2"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $fieldName))) ?>:</strong>
                                                                <span><?= htmlspecialchars($fieldValue) ?></span>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-history fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Status History</h5>
                <p class="text-muted">This lead hasn't had any status changes yet.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Quick Status Change Modal -->
<div class="modal fade" id="quickStatusChangeModal" tabindex="-1" aria-labelledby="quickStatusChangeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="quickStatusChangeModalLabel">Change Lead Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="index.php?action=update_status_with_custom_fields" id="quickStatusChangeForm">
                <div class="modal-body">
                    <input type="hidden" name="lead_id" value="<?= $lead['id'] ?>">
                    
                    <div class="mb-3">
                        <label for="quick_new_status" class="form-label">New Status</label>
                        <select class="form-select" id="quick_new_status" name="new_status" required>
                            <option value="">Select Status</option>
                            <?php
                            $statusModel = new StatusModel();
                            $statuses = $statusModel->all();
                            foreach ($statuses as $status):
                                if ($status['name'] !== $lead['status']):
                                    $customFields = $statusModel->getCustomFieldsByName($status['name']);
                                    $hasFields = count($customFields) > 0;
                            ?>
                                <option value="<?= htmlspecialchars($status['name']) ?>" data-has-fields="<?= $hasFields ? 'true' : 'false' ?>">
                                    <?= htmlspecialchars($status['name']) ?><?= $hasFields ? ' 📝' : '' ?>
                                </option>
                            <?php
                                endif;
                            endforeach;
                            ?>
                        </select>
                        <div class="form-text">
                            <i class="fas fa-info-circle text-info me-1"></i>
                            <span class="text-muted">📝 indicates statuses that require additional information</span>
                        </div>
                    </div>
                    
                    <!-- Dynamic Custom Fields Container -->
                    <div id="quickCustomFieldsContainer"></div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Current status: <strong><?= htmlspecialchars($lead['status'] ?: 'New Lead') ?></strong>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- History Details Modal -->
<div class="modal fade" id="historyDetailsModal" tabindex="-1" aria-labelledby="historyDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="historyDetailsModalLabel">Status Change Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="historyDetailsContent">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
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

.history-row {
    transition: background-color 0.2s ease;
}

.history-row:hover {
    background-color: #f8f9fa;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const quickStatusSelect = document.getElementById('quick_new_status');
    const quickCustomFieldsContainer = document.getElementById('quickCustomFieldsContainer');
    
    // Handle quick status change modal
    if (quickStatusSelect) {
        quickStatusSelect.addEventListener('change', function() {
            const selectedStatus = this.value;
            
            // Clear previous custom fields
            quickCustomFieldsContainer.innerHTML = '';
            
            if (selectedStatus) {
                // Show loading indicator
                quickCustomFieldsContainer.innerHTML = '<div class="text-center py-3"><i class="fas fa-spinner fa-spin me-2"></i>Loading custom fields...</div>';
                
                // Fetch custom fields for the selected status
                fetch(`index.php?action=get_custom_fields_for_status&status=${encodeURIComponent(selectedStatus)}`)
                    .then(response => response.json())
                    .then(data => {
                        quickCustomFieldsContainer.innerHTML = '';
                        
                        if (data.customFields && data.customFields.length > 0) {
                            // Add header for custom fields
                            quickCustomFieldsContainer.insertAdjacentHTML('beforeend', '<div class="alert alert-info mb-3"><i class="fas fa-info-circle me-2"></i>This status requires additional information:</div>');
                            
                            data.customFields.forEach(field => {
                                const fieldHtml = createCustomFieldHtml(field);
                                quickCustomFieldsContainer.insertAdjacentHTML('beforeend', fieldHtml);
                            });
                        } else {
                            // Show message when no custom fields
                            quickCustomFieldsContainer.insertAdjacentHTML('beforeend', '<div class="alert alert-success mb-3"><i class="fas fa-check-circle me-2"></i>No additional fields required for this status.</div>');
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching custom fields:', error);
                        quickCustomFieldsContainer.innerHTML = '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i>Error loading custom fields. Please try again.</div>';
                    });
            }
        });
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
});

function showHistoryDetails(historyId) {
    // This function can be expanded to show more detailed information
    // For now, we'll just show a simple alert
    alert('History details for entry ID: ' + historyId + '\n\nThis feature can be expanded to show more detailed information about the status change.');
}
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
