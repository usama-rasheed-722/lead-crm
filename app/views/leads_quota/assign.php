<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>
        <i class="fas fa-tasks me-2"></i>Assign Leads Quota
    </h2>
    <div>
        <a href="index.php?action=leads_quota_manage" class="btn btn-outline-secondary me-2">
            <i class="fas fa-list me-2"></i>Manage Quotas
        </a>
        <a href="index.php?action=dashboard" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
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
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Assign Daily Leads Quota</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="index.php?action=leads_quota_store">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="user_id" class="form-label">SDR</label>
                                <select class="form-select" id="user_id" name="user_id" required>
                                    <option value="">Select SDR</option>
                                    <?php foreach ($users as $user): ?>
                                        <?php if ($user['role'] === 'sdr'): ?>
                                            <option value="<?= $user['id'] ?>">
                                                <?= htmlspecialchars($user['full_name'] ?: $user['username']) ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="status_id" class="form-label">Status</label>
                                <select class="form-select" id="status_id" name="status_id" required>
                                    <option value="">Select Status</option>
                                    <?php foreach ($statuses as $status): ?>
                                        <option value="<?= $status['id'] ?>">
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
                                <label for="quota_count" class="form-label">Number of Leads</label>
                                <input type="number" class="form-control" id="quota_count" name="quota_count" 
                                       min="1" max="1000" required placeholder="Enter number of leads">
                                <div class="form-text">
                                    Number of leads to assign for this status
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="assigned_date" class="form-label">Assignment Date</label>
                                <input type="date" class="form-control" id="assigned_date" name="assigned_date" 
                                       value="<?= date('Y-m-d') ?>" required>
                                <div class="form-text">
                                    Date for which the quota is assigned
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="explanation" class="form-label">Instructions/Explanation</label>
                                <textarea class="form-control" id="explanation" name="explanation" rows="3" 
                                          placeholder="Add specific instructions or explanation for this quota assignment..."></textarea>
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    This explanation will be visible to the SDR when they view their assigned quota. 
                                    Use this to provide specific instructions, priorities, or context for the quota.
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Note:</strong> The system will automatically assign available leads from the selected status to the SDR for the specified date. 
                        If not enough leads are available, only the available ones will be assigned.
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Assign Quota
                        </button>
                        <a href="index.php?action=leads_quota_manage" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i>Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Instructions</h5>
            </div>
            <div class="card-body">
                <ol class="mb-0">
                    <li>Select an SDR from the dropdown</li>
                    <li>Choose the status for which to assign quota</li>
                    <li>Enter the number of leads to assign</li>
                    <li>Set the assignment date (default: today)</li>
                    <li>Add instructions/explanation (optional)</li>
                    <li>Click "Assign Quota" to create the assignment</li>
                </ol>
                
                <hr>
                
                <h6 class="fw-bold">How it works:</h6>
                <ul class="mb-0 small">
                    <li>System automatically selects available leads from the chosen status</li>
                    <li>Leads are assigned to the SDR for the specified date</li>
                    <li>SDR can view and work on assigned leads</li>
                    <li>Progress is tracked automatically</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add some client-side validation
    const form = document.querySelector('form');
    const quotaCount = document.getElementById('quota_count');
    const statusSelect = document.getElementById('status_id');
    
    form.addEventListener('submit', function(e) {
        const count = parseInt(quotaCount.value);
        if (count < 1 || count > 1000) {
            e.preventDefault();
            alert('Quota count must be between 1 and 1000');
            return false;
        }
        
        if (!statusSelect.value) {
            e.preventDefault();
            alert('Please select a status');
            return false;
        }
    });
    
    // Auto-focus on first field
    document.getElementById('user_id').focus();
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
