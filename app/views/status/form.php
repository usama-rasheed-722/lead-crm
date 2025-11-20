<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>
        <i class="fas fa-tag me-2"></i>
        <?= $action === 'create' ? 'Add New Status' : 'Edit Status' ?>
    </h2>
    <a href="index.php?action=status_management" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Back to Status Management
    </a>
</div>

<?php if (isset($error)): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Status Information</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="index.php?action=<?= $action === 'create' ? 'status_store' : 'status_update&id=' . $status['id'] ?>">
                    <div class="mb-3">
                        <label for="name" class="form-label">Status Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" 
                               value="<?= htmlspecialchars($status['name'] ?? '') ?>" 
                               placeholder="e.g., New Lead, Email Contact, Responded" required>
                        <div class="form-text">Enter a descriptive name for the status.</div>
                    </div>

                    <div class="mb-3">
                        <div for="sequence" class="form-label">
                            Sequence: <input class="form-control" type="number" id="sequence" name="sequence" 
                                   value="<?= ($status['sequence'] ?? 0) ?>">
                        </div>
                        <div class="form-text">Sequence number for ordering statuses.</div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="restrict_bulk_update" name="restrict_bulk_update" 
                                   value="1" <?= ($status['restrict_bulk_update'] ?? false) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="restrict_bulk_update">
                                Restrict Bulk Status Updates
                            </label>
                            <div class="form-text">When enabled, users cannot change to this status using bulk update operations.</div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_default" name="is_default" 
                                   value="1" <?= ($status['is_default'] ?? false) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_default">
                                Set as Default Status
                            </label>
                            <div class="form-text">When enabled, this status will be automatically selected for new leads. Only one status can be the default.</div>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="index.php?action=status_management" class="btn btn-secondary me-md-2">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>
                            <?= $action === 'create' ? 'Create Status' : 'Update Status' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Custom Fields Management (only for edit mode) -->
        <?php if ($action === 'edit' && isset($status['id'])): ?>
        <div class="card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Custom Fields</h5>
                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addCustomFieldModal">
                    <i class="fas fa-plus me-1"></i>Add Field
                </button>
            </div>
            <div class="card-body">
                <?php if (!empty($customFields)): ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Field Label</th>
                                    <th>Field Name</th>
                                    <th>Type</th>
                                    <th>Required</th>
                                    <th>Order</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($customFields as $field): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($field['field_label']) ?></td>
                                        <td><code><?= htmlspecialchars($field['field_name']) ?></code></td>
                                        <td>
                                            <span class="badge bg-secondary"><?= htmlspecialchars($field['field_type']) ?></span>
                                        </td>
                                        <td>
                                            <?php if ($field['is_required']): ?>
                                                <span class="badge bg-danger">Required</span>
                                            <?php else: ?>
                                                <span class="badge bg-light text-dark">Optional</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= $field['field_order'] ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                                    onclick="editCustomField(<?= htmlspecialchars(json_encode($field)) ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <a href="index.php?action=delete_custom_field&id=<?= $field['id'] ?>&status_id=<?= $status['id'] ?>" 
                                               class="btn btn-sm btn-outline-danger"
                                               onclick="return confirm('Are you sure you want to delete this custom field?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-info-circle fa-2x mb-2"></i>
                        <p>No custom fields defined for this status.</p>
                        <p>Click "Add Field" to create custom fields that will appear when users change to this status.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Custom Field Modal -->
<div class="modal fade" id="addCustomFieldModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Custom Field</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="index.php?action=create_custom_field">
                <div class="modal-body">
                    <input type="hidden" name="status_id" value="<?= $status['id'] ?? '' ?>">
                    
                    <div class="mb-3">
                        <label for="field_label" class="form-label">Field Label <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="field_label" name="field_label" required>
                        <div class="form-text">Display name for the field (e.g., "Meeting Date")</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="field_name" class="form-label">Field Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="field_name" name="field_name" required>
                        <div class="form-text">Internal field name (e.g., "meeting_date") - lowercase, no spaces</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="field_type" class="form-label">Field Type <span class="text-danger">*</span></label>
                        <select class="form-select" id="field_type" name="field_type" required>
                            <option value="text">Text</option>
                            <option value="textarea">Textarea</option>
                            <option value="select">Select Dropdown</option>
                            <option value="date">Date</option>
                            <option value="number">Number</option>
                            <option value="email">Email</option>
                            <option value="url">URL</option>
                        </select>
                    </div>
                    
                    <div class="mb-3" id="field_options_container" style="display: none;">
                        <label for="field_options" class="form-label">Options</label>
                        <textarea class="form-control" id="field_options" name="field_options" rows="3" 
                                  placeholder="Enter each option on a new line"></textarea>
                        <div class="form-text">Enter each option on a separate line (only for select fields)</div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_required" name="is_required" value="1">
                            <label class="form-check-label" for="is_required">
                                Required Field
                            </label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="field_order" class="form-label">Display Order</label>
                        <input type="number" class="form-control" id="field_order" name="field_order" value="0">
                        <div class="form-text">Lower numbers appear first</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Field</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fieldTypeSelect = document.getElementById('field_type');
    const optionsContainer = document.getElementById('field_options_container');
    
    fieldTypeSelect.addEventListener('change', function() {
        if (this.value === 'select') {
            optionsContainer.style.display = 'block';
        } else {
            optionsContainer.style.display = 'none';
        }
    });
    
    // Auto-generate field name from label
    const fieldLabel = document.getElementById('field_label');
    const fieldName = document.getElementById('field_name');
    
    fieldLabel.addEventListener('input', function() {
        if (!fieldName.value) {
            const generatedName = this.value
                .toLowerCase()
                .replace(/[^a-z0-9\s]/g, '')
                .replace(/\s+/g, '_')
                .trim();
            fieldName.value = generatedName;
        }
    });
});

function editCustomField(field) {
    // Populate the modal with field data
    document.getElementById('field_label').value = field.field_label;
    document.getElementById('field_name').value = field.field_name;
    document.getElementById('field_type').value = field.field_type;
    document.getElementById('field_options').value = field.field_options || '';
    document.getElementById('is_required').checked = field.is_required == 1;
    document.getElementById('field_order').value = field.field_order;
    
    // Show options container if needed
    const optionsContainer = document.getElementById('field_options_container');
    if (field.field_type === 'select') {
        optionsContainer.style.display = 'block';
    } else {
        optionsContainer.style.display = 'none';
    }
    
    // Change form action to update
    const form = document.querySelector('#addCustomFieldModal form');
    form.action = 'index.php?action=update_custom_field&id=' + field.id;
    
    // Change modal title and button text
    document.querySelector('#addCustomFieldModal .modal-title').textContent = 'Edit Custom Field';
    document.querySelector('#addCustomFieldModal .btn-primary').textContent = 'Update Field';
    
    // Show modal
    new bootstrap.Modal(document.getElementById('addCustomFieldModal')).show();
}
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
