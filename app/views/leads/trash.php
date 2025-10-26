<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-trash-alt me-2"></i>Trash - Deleted Leads</h2>
    <div>
        <a href="index.php?action=leads" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Leads
        </a>
    </div>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle me-2"></i>
        <?= htmlspecialchars($_GET['success']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <?= htmlspecialchars($_GET['error']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header bg-danger text-white">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-trash me-2"></i>Deleted Leads (<?= $total ?>)
            </h5>
            <div>
                <button class="btn btn-sm btn-light" onclick="restoreSelected()">
                    <i class="fas fa-undo me-1"></i>Restore Selected
                </button>
                <button class="btn btn-sm btn-light" onclick="deleteSelected()">
                    <i class="fas fa-trash me-1"></i>Delete Permanently
                </button>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <?php if (!empty($leads)): ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th width="50">
                                <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                            </th>
                            <th>Lead ID</th>
                            <th>Name</th>
                            <th>Company</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>SDR</th>
                            <th>Status</th>
                            <th>Deleted At</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($leads as $lead): ?>
                            <tr>
                                <td>
                                    <input type="checkbox" class="lead-checkbox" value="<?= $lead['id'] ?>">
                                </td>
                                <td><?= htmlspecialchars($lead['lead_id'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($lead['name'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($lead['company'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($lead['email'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($lead['phone'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($lead['sdr_name'] ?? 'N/A') ?></td>
                                <td>
                                    <span class="badge bg-secondary">
                                        <?= htmlspecialchars($lead['status_name'] ?? 'N/A') ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($lead['deleted_at'])): ?>
                                        <?= date('M d, Y h:i A', strtotime($lead['deleted_at'])) ?>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <a href="index.php?action=restore_lead&id=<?= $lead['id'] ?>" 
                                       class="btn btn-sm btn-success" 
                                       onclick="return confirm('Are you sure you want to restore this lead?')">
                                        <i class="fas fa-undo"></i> Restore
                                    </a>
                                    <a href="index.php?action=permanent_delete&id=<?= $lead['id'] ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Are you sure you want to permanently delete this lead? This action cannot be undone!')">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <nav class="m-3">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?= $page == 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="index.php?action=trash&page=<?= $page - 1 ?>">
                                <i class="fas fa-chevron-left"></i> Previous
                            </a>
                        </li>
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link" href="index.php?action=trash&page=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= $page == $totalPages ? 'disabled' : '' ?>">
                            <a class="page-link" href="index.php?action=trash&page=<?= $page + 1 ?>">
                                Next <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php else: ?>
            <div class="text-center p-5">
                <i class="fas fa-trash fa-3x text-muted mb-3"></i>
                <h5>Trash is Empty</h5>
                <p class="text-muted">No deleted leads found.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.lead-checkbox');
    checkboxes.forEach(cb => cb.checked = selectAll.checked);
}

function getSelectedIds() {
    const checked = document.querySelectorAll('.lead-checkbox:checked');
    return Array.from(checked).map(cb => cb.value);
}

function restoreSelected() {
    const ids = getSelectedIds();
    if (ids.length === 0) {
        alert('Please select at least one lead to restore.');
        return;
    }
    
    if (confirm('Are you sure you want to restore ' + ids.length + ' lead(s)?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'index.php?action=bulk_restore';
        
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'lead_ids';
        input.value = ids.join(',');
        form.appendChild(input);
        
        document.body.appendChild(form);
        form.submit();
    }
}

function deleteSelected() {
    const ids = getSelectedIds();
    if (ids.length === 0) {
        alert('Please select at least one lead to permanently delete.');
        return;
    }
    
    if (confirm('WARNING: Are you sure you want to permanently delete ' + ids.length + ' lead(s)? This action cannot be undone!')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'index.php?action=bulk_permanent_delete';
        
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'lead_ids';
        input.value = ids.join(',');
        form.appendChild(input);
        
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
