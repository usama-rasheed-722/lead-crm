<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>
        <i class="fas fa-<?= $action === 'edit' ? 'edit' : 'plus' ?> me-2"></i>
        <?= $action === 'edit' ? 'Edit Lead' : 'Add New Lead' ?>
    </h2>
    <a href="index.php?action=leads" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Back to Leads
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="lead-form">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="index.php?action=<?= $action === 'edit' ? 'lead_update&id=' . $lead['id'] : 'lead_store' ?>" 
                  class="needs-validation" novalidate>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name *</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?= htmlspecialchars($lead['name'] ?? '') ?>" required>
                            <div class="invalid-feedback">
                                Please provide a name.
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="company" class="form-label">Company</label>
                            <input type="text" class="form-control" id="company" name="company" 
                                   value="<?= htmlspecialchars($lead['company'] ?? '') ?>">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?= htmlspecialchars($lead['email'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="<?= htmlspecialchars($lead['phone'] ?? '') ?>">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="linkedin" class="form-label">LinkedIn</label>
                            <input type="url" class="form-control" id="linkedin" name="linkedin" 
                                   value="<?= htmlspecialchars($lead['linkedin'] ?? '') ?>" 
                                   placeholder="https://linkedin.com/in/username">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="website" class="form-label">Website</label>
                            <input type="url" class="form-control" id="website" name="website" 
                                   value="<?= htmlspecialchars($lead['website'] ?? '') ?>" 
                                   placeholder="https://example.com">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="clutch" class="form-label">Clutch Profile</label>
                            <input type="url" class="form-control" id="clutch" name="clutch" 
                                   value="<?= htmlspecialchars($lead['clutch'] ?? '') ?>" 
                                   placeholder="https://clutch.co/profile/company">
                        </div>
                    </div>
                    <?php if (auth_user()['role'] === 'admin'): ?>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="sdr_id" class="form-label">SDR</label>
                            <select class="form-select" id="sdr_id" name="sdr_id">
                                <option value="">Select SDR</option>
                                <?php foreach ($users as $user): ?>
                                    <?php if ($user['role'] === 'sdr'): ?>
                                        <option value="<?= $user['id'] ?>" 
                                                <?= ($lead['sdr_id'] ?? '') == $user['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($user['full_name'] ?: $user['username']) ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="notes" class="form-label">Notes</label>
                    <textarea class="form-control" id="notes" name="notes" rows="4" 
                              placeholder="Add any additional notes about this lead..."><?= htmlspecialchars($lead['notes'] ?? '') ?></textarea>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="index.php?action=leads" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-2"></i>Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>
                        <?= $action === 'edit' ? 'Update Lead' : 'Create Lead' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Auto-generate Lead ID preview
document.addEventListener('DOMContentLoaded', function() {
    const sdrSelect = document.getElementById('sdr_id');
    const nameInput = document.getElementById('name');
    
    if (sdrSelect && nameInput) {
        function updateLeadIdPreview() {
            const sdrId = sdrSelect.value;
            const name = nameInput.value;
            
            if (sdrId && name) {
                // This would normally be done server-side, but we can show a preview
                const preview = `SDR${sdrId}-XXXXX`;
                console.log('Lead ID will be generated as:', preview);
            }
        }
        
        sdrSelect.addEventListener('change', updateLeadIdPreview);
        nameInput.addEventListener('input', updateLeadIdPreview);
    }
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>