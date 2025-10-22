<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>
        <i class="fas fa-user me-2"></i>Lead Details
    </h2>
    <div>
        <button type="button" class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#quickStatusChangeModal">
            <i class="fas fa-sync me-2"></i>Change Status
        </button>
        <a href="index.php?action=lead_status_history&id=<?= $lead['id'] ?>" class="btn btn-info me-2">
            <i class="fas fa-history me-2"></i>Full Status History
        </a>
        <a href="index.php?action=find_duplicates&id=<?= $lead['id'] ?>" class="btn btn-warning me-2">
            <i class="fas fa-search me-2"></i>Find Duplicates
        </a>
        <a href="index.php?action=lead_edit&id=<?= $lead['id'] ?>" class="btn btn-outline-primary me-2">
            <i class="fas fa-edit me-2"></i>Edit Lead
        </a>
        <a href="index.php?action=lead_add" class="btn btn-outline-primary me-2">
            <i class="fas fa-plus me-2"></i>Add Lead
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

<div class="row">
    <!-- Lead Information -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Lead Information</h5>
                <?php
                $dup = $lead['duplicate_status'];
                $dupClass = 'secondary';
                $dupIcon = '❓';
                if ($dup === 'unique') { $dupClass = 'success'; $dupIcon = '✅'; }
                elseif ($dup === 'duplicate') { $dupClass = 'warning'; $dupIcon = '🔁'; }
                elseif ($dup === 'incomplete') { $dupClass = 'danger'; $dupIcon = '⚠️'; }
                ?>
                <span class="badge bg-<?= $dupClass ?>">
                    <?= $dupIcon ?> <?= ucfirst($dup) ?>
                </span>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="fw-bold">Lead ID:</td>
                                <td><?= htmlspecialchars($lead['lead_id']) ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Name:</td>
                                <td><?= htmlspecialchars($lead['name'] ?: 'N/A') ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Job Title:</td>
                                <td><?= htmlspecialchars($lead['job_title'] ?: 'N/A') ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Company:</td>
                                <td><?= htmlspecialchars($lead['company'] ?: 'N/A') ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Email:</td>
                                <td>
                                    <?php if ($lead['email']): ?>
                                        <a href="mailto:<?= htmlspecialchars($lead['email']) ?>" class="text-decoration-none">
                                            <?= htmlspecialchars($lead['email']) ?>
                                        </a>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Phone:</td>
                                <td>
                                    <?php if ($lead['phone']): ?>
                                        <a href="tel:<?= htmlspecialchars($lead['phone']) ?>" class="text-decoration-none">
                                            <?= htmlspecialchars($lead['phone']) ?>
                                        </a>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="fw-bold">LinkedIn:</td>
                                <td>
                                    <?php if ($lead['linkedin']): ?>
                                        <a href="<?= htmlspecialchars($lead['linkedin']) ?>" target="_blank" class="text-decoration-none">
                                            <i class="fab fa-linkedin me-1"></i>
                                            View Profile
                                        </a>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Website:</td>
                                <td>
                                    <?php if ($lead['website']): ?>
                                        <a href="<?= htmlspecialchars($lead['website']) ?>" target="_blank" class="text-decoration-none">
                                            <i class="fas fa-external-link-alt me-1"></i>
                                            <?= htmlspecialchars($lead['website']) ?>
                                        </a>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Clutch:</td>
                                <td>
                                    <?php if ($lead['clutch']): ?>
                                        <a href="<?= htmlspecialchars($lead['clutch']) ?>" target="_blank" class="text-decoration-none">
                                            <i class="fas fa-external-link-alt me-1"></i>
                                            View Profile
                                        </a>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Industry:</td>
                                <td><?= htmlspecialchars($lead['industry'] ?: 'N/A') ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Lead Source:</td>
                                <td><?= htmlspecialchars($lead['lead_source_name'] ?: 'N/A') ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Tier:</td>
                                <td><?= htmlspecialchars($lead['tier'] ?: 'N/A') ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Lead Status:</td>
                                <td><?= htmlspecialchars($lead['lead_status'] ?: 'N/A') ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Instagram:</td>
                                <td><?= htmlspecialchars($lead['insta'] ?: 'N/A') ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Social Profile:</td>
                                <td><?= htmlspecialchars($lead['social_profile'] ?: 'N/A') ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Address:</td>
                                <td><?= htmlspecialchars($lead['address'] ?: 'N/A') ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Country:</td>
                                <td><?= htmlspecialchars($lead['country'] ?: 'N/A') ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Whatsapp:</td>
                                <td><?= htmlspecialchars($lead['whatsapp'] ?: 'N/A') ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Next Step:</td>
                                <td><?= htmlspecialchars($lead['next_step'] ?: 'N/A') ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Other:</td>
                                <td><?= htmlspecialchars($lead['other'] ?: 'N/A') ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Status:</td>
                                <td><?= htmlspecialchars($lead['status_name'] ?: 'N/A') ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">SDR:</td>
                                <td><?= htmlspecialchars($lead['sdr_name'] ?: 'N/A') ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Created:</td>
                                <td><?= date('M j, Y g:i A', strtotime($lead['created_at'])) ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Updated:</td>
                                <td><?= date('M j, Y g:i A', strtotime($lead['updated_at'])) ?></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <?php if ($lead['notes']): ?>
                <div class="mt-4">
                    <h6 class="fw-bold">Notes:</h6>
                    <div class="bg-light p-3 rounded">
                        <?= nl2br(htmlspecialchars($lead['notes'])) ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <?php if ($lead['email']): ?>
                        <a href="mailto:<?= htmlspecialchars($lead['email']) ?>" class="btn btn-outline-primary">
                            <i class="fas fa-envelope me-2"></i>Send Email
                        </a>
                    <?php endif; ?>
                    <?php if ($lead['phone']): ?>
                        <a href="tel:<?= htmlspecialchars($lead['phone']) ?>" class="btn btn-outline-success">
                            <i class="fas fa-phone me-2"></i>Call
                        </a>
                    <?php endif; ?>
                    <?php if ($lead['linkedin']): ?>
                        <a href="<?= htmlspecialchars($lead['linkedin']) ?>" target="_blank" class="btn btn-outline-info">
                            <i class="fab fa-linkedin me-2"></i>LinkedIn
                        </a>
                    <?php endif; ?>
                    <?php if ($lead['website']): ?>
                        <a href="<?= htmlspecialchars($lead['website']) ?>" target="_blank" class="btn btn-outline-secondary">
                            <i class="fas fa-globe me-2"></i>Website
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Change Status -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Change Status</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="index.php?action=update_status_with_custom_fields" id="statusChangeForm">
                    <input type="hidden" name="lead_id" value="<?= $lead['id'] ?>">
                    <div class="mb-3">
                        <label for="new_status_id" class="form-label">New Status</label>
                        <select class="form-select" id="new_status_id" name="new_status_id" required>
                            <option value="">Select Status</option>
                            <?php
                            $statusModel = new StatusModel();
                            $statuses = $statusModel->all();
                            foreach ($statuses as $status):
                                if ($status['id'] != $lead['status_id']):
                                    $customFields = $statusModel->getCustomFieldsByName($status['name']);
                                    $hasFields = count($customFields) > 0;
                            ?>
                                <option value="<?= $status['id'] ?>" data-has-fields="<?= $hasFields ? 'true' : 'false' ?>">
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
                    <div id="customFieldsContainer"></div>
                    
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-sync me-2"></i>Update Status
                    </button>
                </form>
            </div>
        </div>

        <!-- Add Note -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Add Note</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="index.php?action=notes_add">
                    <input type="hidden" name="lead_id" value="<?= $lead['id'] ?>">
                    <div class="mb-3">
                        <label for="note_type" class="form-label">Type</label>
                        <select class="form-select" id="note_type" name="type" required>
                            <option value="note">Note</option>
                            <option value="call">Call</option>
                            <option value="email">Email</option>
                            <option value="update">Update</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="note_content" class="form-label">Content</label>
                        <textarea class="form-control" id="note_content" name="content" rows="3" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-plus me-2"></i>Add Note
                    </button>
                </form>
            </div>
        </div>

        <!-- Status History -->
        <?php if (!empty($statusHistory)): ?>
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Status History</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Changed At</th>
                                <th>Old</th>
                                <th>New</th>
                                <th>By</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($statusHistory as $h): ?>
                                <tr>
                                    <td><?= date('Y-m-d H:i', strtotime($h['changed_at'])) ?></td>
                                    <td><?= htmlspecialchars($h['old_status'] ?: '—') ?></td>
                                    <td>
                                        <span class="badge bg-secondary"><?= htmlspecialchars($h['new_status']) ?></span>
                                        <?php if (!empty($h['custom_fields_data'])): ?>
                                            <?php 
                                            $customData = json_decode($h['custom_fields_data'], true);
                                            if ($customData && count($customData) > 0):
                                            ?>
                                                <div class="mt-1">
                                                    <small class="text-muted">
                                                        <i class="fas fa-info-circle me-1"></i>
                                                        <a href="#" class="text-decoration-none" data-bs-toggle="collapse" data-bs-target="#customFields_<?= $h['id'] ?>">
                                                            Custom Fields (<?= count($customData) ?>)
                                                        </a>
                                                    </small>
                                                    <div class="collapse mt-2" id="customFields_<?= $h['id'] ?>">
                                                        <div class="card card-body p-2">
                                                            <?php foreach ($customData as $fieldName => $fieldValue): ?>
                                                                <div class="row">
                                                                    <div class="col-4"><small class="fw-bold"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $fieldName))) ?>:</small></div>
                                                                    <div class="col-8"><small><?= htmlspecialchars($fieldValue) ?></small></div>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($h['full_name'] ?: $h['username'] ?: 'System') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Notes Section -->
<?php if (!empty($notes)): ?>
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Activity & Notes</h5>
            </div>
            <div class="card-body">
                <?php foreach ($notes as $note): ?>
                    <div class="note-item">
                        <div class="note-meta">
                            <?php
                            $icon = 'sticky-note';
                            if ($note['type'] === 'call') $icon = 'phone';
                            elseif ($note['type'] === 'email') $icon = 'envelope';
                            elseif ($note['type'] === 'update') $icon = 'edit';
                            ?>
                            <i class="fas fa-<?= $icon ?> me-1"></i>
                            <strong><?= ucfirst($note['type']) ?></strong> by 
                            <?= htmlspecialchars($note['full_name'] ?: $note['username']) ?> 
                            on <?= date('M j, Y g:i A', strtotime($note['created_at'])) ?>
                        </div>
                        <div><?= nl2br(htmlspecialchars($note['content'])) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

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
                        <label for="quick_new_status_id" class="form-label">New Status</label>
                        <select class="form-select" id="quick_new_status_id" name="new_status_id" required>
                            <option value="">Select Status</option>
                            <?php
                            $statusModel = new StatusModel();
                            $statuses = $statusModel->all();
                            foreach ($statuses as $status):
                                if ($status['id'] != $lead['status_id']):
                                    $customFields = $statusModel->getCustomFieldsByName($status['name']);
                                    $hasFields = count($customFields) > 0;
                            ?>
                                <option value="<?= $status['id'] ?>" data-has-fields="<?= $hasFields ? 'true' : 'false' ?>">
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
                        Current status: <strong><?= htmlspecialchars($lead['status_name'] ?: 'New Lead') ?></strong>
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
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const statusSelect = document.getElementById('new_status_id');
    const customFieldsContainer = document.getElementById('customFieldsContainer');
    const quickStatusSelect = document.getElementById('quick_new_status_id');
    const quickCustomFieldsContainer = document.getElementById('quickCustomFieldsContainer');
    
    // Handle sidebar status change
    if (statusSelect) {
        statusSelect.addEventListener('change', function() {
            const selectedStatusId = this.value;
            const selectedStatusName = this.selectedOptions[0]?.text?.replace(' 📝', '') || '';
            
            // Clear previous custom fields
            customFieldsContainer.innerHTML = '';
            
            if (selectedStatusId) {
                // Show loading indicator
                customFieldsContainer.innerHTML = '<div class="text-center py-3"><i class="fas fa-spinner fa-spin me-2"></i>Loading custom fields...</div>';
                
                // Fetch custom fields for the selected status
                fetch(`index.php?action=get_custom_fields_for_status&status=${encodeURIComponent(selectedStatusName)}`)
                    .then(response => response.json())
                    .then(data => {
                        customFieldsContainer.innerHTML = '';
                        
                        if (data.customFields && data.customFields.length > 0) {
                            // Add header for custom fields
                            customFieldsContainer.insertAdjacentHTML('beforeend', '<div class="alert alert-info mb-3"><i class="fas fa-info-circle me-2"></i>This status requires additional information:</div>');
                            
                            data.customFields.forEach(field => {
                                const fieldHtml = createCustomFieldHtml(field);
                                customFieldsContainer.insertAdjacentHTML('beforeend', fieldHtml);
                            });
                        } else {
                            // Show message when no custom fields
                            customFieldsContainer.insertAdjacentHTML('beforeend', '<div class="alert alert-success mb-3"><i class="fas fa-check-circle me-2"></i>No additional fields required for this status.</div>');
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching custom fields:', error);
                        customFieldsContainer.innerHTML = '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i>Error loading custom fields. Please try again.</div>';
                    });
            }
        });
    }
    
    // Handle quick status change modal
    if (quickStatusSelect) {
        quickStatusSelect.addEventListener('change', function() {
            const selectedStatusId = this.value;
            const selectedStatusName = this.selectedOptions[0]?.text?.replace(' 📝', '') || '';
            
            // Clear previous custom fields
            quickCustomFieldsContainer.innerHTML = '';
            
            if (selectedStatusId) {
                // Show loading indicator
                quickCustomFieldsContainer.innerHTML = '<div class="text-center py-3"><i class="fas fa-spinner fa-spin me-2"></i>Loading custom fields...</div>';
                
                // Fetch custom fields for the selected status
                fetch(`index.php?action=get_custom_fields_for_status&status=${encodeURIComponent(selectedStatusName)}`)
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
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>