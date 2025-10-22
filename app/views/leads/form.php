<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>
        <i class="fas fa-<?= $action === 'edit' ? 'edit' : 'plus' ?> me-2"></i>
        <?= $action === 'edit' ? 'Edit Lead' : 'Add New Lead' ?>
    </h2>
  <div>
  <a href="index.php?action=lead_add" class="btn btn-outline-primary me-2">
        <i class="fas fa-plus me-2"></i>Add Lead
    </a>
  <a href="index.php?action=leads" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Back to Leads
    </a>

  </div>
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
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="job_title" class="form-label">Job Title</label>
                            <input type="text" class="form-control" id="job_title" name="job_title" 
                                   value="<?= htmlspecialchars($lead['job_title'] ?? '') ?>">
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
                                        <option value="<?= $user['sdr_id'] ?? $user['id'] ?>" 
                                                <?= ($lead['sdr_id'] ?? '') == ($user['sdr_id'] ?? $user['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($user['full_name'] ?: $user['username']) ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="industry" class="form-label">Industry</label>
                            <input type="text" class="form-control" id="industry" name="industry" 
                                   value="<?= htmlspecialchars($lead['industry'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="lead_source_id" class="form-label">Lead Source</label>
                            <select class="form-select" id="lead_source_id" name="lead_source_id">
                                <option value="">Select Lead Source</option>
                                <?php foreach ($leadSources as $source): ?>
                                    <option value="<?= htmlspecialchars($source['id']) ?>" 
                                            <?= ($lead['lead_source_id'] ?? '') == $source['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($source['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="tier" class="form-label">Tier</label>
                            <input type="text" class="form-control" id="tier" name="tier" 
                                   value="<?= htmlspecialchars($lead['tier'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="lead_status" class="form-label">Lead Status</label>
                            <input type="text" class="form-control" id="lead_status" name="lead_status" 
                                   value="<?= htmlspecialchars($lead['lead_status'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="status_id" class="form-label">Status</label>
                            <select class="form-select" id="status_id" name="status_id">
                                <option value="">Select Status</option>
                                <?php foreach ($statuses as $status): ?>
                                    <option value="<?= $status['id'] ?>" 
                                        <?= ($lead['status_id'] ?? '') == $status['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($status['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="insta" class="form-label">Instagram</label>
                            <input type="text" class="form-control" id="insta" name="insta" 
                                   value="<?= htmlspecialchars($lead['insta'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="social_profile" class="form-label">Social Profile</label>
                            <input type="text" class="form-control" id="social_profile" name="social_profile" 
                                   value="<?= htmlspecialchars($lead['social_profile'] ?? '') ?>">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <input type="text" class="form-control" id="address" name="address" 
                                   value="<?= htmlspecialchars($lead['address'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="country" class="form-label">Country</label>
                            <input type="text" class="form-control" id="country" name="country" 
                                   value="<?= htmlspecialchars($lead['country'] ?? '') ?>">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="whatsapp" class="form-label">Whatsapp</label>
                            <input type="text" class="form-control" id="whatsapp" name="whatsapp" 
                                   value="<?= htmlspecialchars($lead['whatsapp'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="next_step" class="form-label">Next Step</label>
                            <input type="text" class="form-control" id="next_step" name="next_step" 
                                   value="<?= htmlspecialchars($lead['next_step'] ?? '') ?>">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="other" class="form-label">Other</label>
                            <input type="text" class="form-control" id="other" name="other" 
                                   value="<?= htmlspecialchars($lead['other'] ?? '') ?>">
                        </div>
                    </div>
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