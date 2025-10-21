<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>
        <i class="fas fa-<?= $action === 'create' ? 'plus' : 'edit' ?> me-2"></i>
        <?= $action === 'create' ? 'Add New Lead Source' : 'Edit Lead Source' ?>
    </h2>
    <div>
        <a href="index.php?action=lead_sources" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Lead Sources
        </a>
    </div>
</div>

<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <?= htmlspecialchars($_GET['error']) ?>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Lead Source Information</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="index.php?action=lead_source_<?= $action === 'create' ? 'store' : 'update' ?>">
                    <?php if ($action === 'edit'): ?>
                        <input type="hidden" name="id" value="<?= $leadSource['id'] ?>">
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" 
                               value="<?= htmlspecialchars($leadSource['name'] ?? '') ?>" 
                               required maxlength="100" placeholder="Enter lead source name">
                        <div class="form-text">A unique name for this lead source (max 100 characters)</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" 
                                  placeholder="Enter a description for this lead source"><?= htmlspecialchars($leadSource['description'] ?? '') ?></textarea>
                        <div class="form-text">Optional description to help identify this lead source</div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                   <?= ($leadSource['is_active'] ?? true) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_active">
                                Active
                            </label>
                        </div>
                        <div class="form-text">Inactive lead sources will not be available for selection in new leads</div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="index.php?action=lead_sources" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>
                            <?= $action === 'create' ? 'Create Lead Source' : 'Update Lead Source' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Information</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Lead Sources</strong> help you track where your leads are coming from.
                </div>
                
                <h6>Best Practices:</h6>
                <ul class="list-unstyled">
                    <li><i class="fas fa-check text-success me-2"></i>Use clear, descriptive names</li>
                    <li><i class="fas fa-check text-success me-2"></i>Keep names consistent</li>
                    <li><i class="fas fa-check text-success me-2"></i>Add descriptions for clarity</li>
                    <li><i class="fas fa-check text-success me-2"></i>Deactivate instead of deleting when possible</li>
                </ul>
                
                <?php if ($action === 'edit' && $leadSource): ?>
                    <hr>
                    <h6>Lead Source Details:</h6>
                    <p class="mb-1"><strong>Created:</strong> <?= date('M j, Y g:i A', strtotime($leadSource['created_at'])) ?></p>
                    <?php if ($leadSource['updated_at'] !== $leadSource['created_at']): ?>
                        <p class="mb-0"><strong>Last Updated:</strong> <?= date('M j, Y g:i A', strtotime($leadSource['updated_at'])) ?></p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    const form = document.querySelector('form');
    const nameInput = document.getElementById('name');
    
    form.addEventListener('submit', function(e) {
        const name = nameInput.value.trim();
        
        if (name.length === 0) {
            e.preventDefault();
            alert('Please enter a lead source name.');
            nameInput.focus();
            return;
        }
        
        if (name.length > 100) {
            e.preventDefault();
            alert('Lead source name must be 100 characters or less.');
            nameInput.focus();
            return;
        }
    });
    
    // Auto-focus on name field
    nameInput.focus();
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
