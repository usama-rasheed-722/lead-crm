<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>
        <i class="fas fa-search me-2"></i>Find Duplicates for Lead: <?= htmlspecialchars($lead['lead_id']) ?>
    </h2>
    <a href="index.php?action=lead_view&id=<?= $lead['id'] ?>" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Back to Lead
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

<!-- Primary Lead Info -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Primary Lead</h5>
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
                        <td class="fw-bold">Company:</td>
                        <td><?= htmlspecialchars($lead['company'] ?: 'N/A') ?></td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Email:</td>
                        <td><?= htmlspecialchars($lead['email'] ?: 'N/A') ?></td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-borderless">
                    <tr>
                        <td class="fw-bold">Phone:</td>
                        <td><?= htmlspecialchars($lead['phone'] ?: 'N/A') ?></td>
                    </tr>
                    <tr>
                        <td class="fw-bold">LinkedIn:</td>
                        <td><?= htmlspecialchars($lead['linkedin'] ?: 'N/A') ?></td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Website:</td>
                        <td><?= htmlspecialchars($lead['website'] ?: 'N/A') ?></td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Clutch:</td>
                        <td><?= htmlspecialchars($lead['clutch'] ?: 'N/A') ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Duplicates -->
<?php if (!empty($duplicates)): ?>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Found <?= count($duplicates) ?> Duplicate(s)</h5>
            <button type="button" class="btn btn-warning" id="mergeSelectedBtn" disabled>
                <i class="fas fa-compress me-2"></i>Merge Selected
            </button>
        </div>
        <div class="card-body p-0">
            <form id="mergeForm" method="POST" action="index.php?action=merge_duplicates&id=<?= $lead['id'] ?>">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>
                                    <input type="checkbox" id="selectAllDups" class="form-check-input">
                                </th>
                                <th>Lead ID</th>
                                <th>Name</th>
                                <th>Company</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Match Type</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($duplicates as $dup): ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" class="form-check-input duplicate-checkbox" name="duplicate_ids[]" value="<?= $dup['id'] ?>">
                                    </td>
                                    <td>
                                        <a href="index.php?action=lead_view&id=<?= $dup['id'] ?>" class="text-decoration-none fw-bold">
                                            <?= htmlspecialchars($dup['lead_id']) ?>
                                        </a>
                                    </td>
                                    <td><?= htmlspecialchars($dup['name'] ?: 'N/A') ?></td>
                                    <td><?= htmlspecialchars($dup['company'] ?: 'N/A') ?></td>
                                    <td><?= htmlspecialchars($dup['email'] ?: 'N/A') ?></td>
                                    <td><?= htmlspecialchars($dup['phone'] ?: 'N/A') ?></td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?= ucfirst($dup['match_type']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="index.php?action=lead_view&id=<?= $dup['id'] ?>" class="btn btn-sm btn-outline-primary" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </form>
        </div>
    </div>
<?php else: ?>
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="fas fa-search fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">No Duplicates Found</h5>
            <p class="text-muted">This lead appears to be unique based on the duplicate detection criteria.</p>
            <a href="index.php?action=lead_view&id=<?= $lead['id'] ?>" class="btn btn-primary">
                <i class="fas fa-arrow-left me-2"></i>Back to Lead
            </a>
        </div>
    </div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('selectAllDups');
    const duplicateCheckboxes = document.querySelectorAll('.duplicate-checkbox');
    const mergeSelectedBtn = document.getElementById('mergeSelectedBtn');
    const mergeForm = document.getElementById('mergeForm');

    // Select all functionality
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            duplicateCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateMergeButton();
        });
    }

    // Individual checkbox change
    duplicateCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateMergeButton();
            updateSelectAllState();
        });
    });

    function updateMergeButton() {
        const checkedBoxes = document.querySelectorAll('.duplicate-checkbox:checked');
        if (mergeSelectedBtn) {
            mergeSelectedBtn.disabled = checkedBoxes.length === 0;
        }
    }

    function updateSelectAllState() {
        const checkedBoxes = document.querySelectorAll('.duplicate-checkbox:checked');
        const totalBoxes = duplicateCheckboxes.length;
        
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

    // Merge functionality
    if (mergeSelectedBtn) {
        mergeSelectedBtn.addEventListener('click', function() {
            const checkedBoxes = document.querySelectorAll('.duplicate-checkbox:checked');
            if (checkedBoxes.length === 0) return;

            if (confirm(`Are you sure you want to merge ${checkedBoxes.length} selected duplicate(s) into the primary lead? This action cannot be undone.`)) {
                mergeForm.submit();
            }
        });
    }
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
