<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-users me-2"></i>Leads Management</h2>
    <a href="index.php?action=lead_add" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Add New Lead
    </a>
</div>

<!-- Search and Filters -->
<div class="search-form">
    <form method="GET" action="index.php">
        <input type="hidden" name="action" value="leads">
        <div class="row g-3">
            <div class="col-md-4">
                <label for="search" class="form-label">Search</label>
                <input type="text" class="form-control" id="search" name="search" 
                       value="<?= htmlspecialchars($search ?? '') ?>" 
                       placeholder="Search by name, company, email, website...">
            </div>
            <div class="col-md-2">
                <label for="sdr_id" class="form-label">SDR</label>
                <select class="form-select" id="sdr_id" name="sdr_id">
                    <option value="">All SDRs</option>
                    <?php foreach ($users as $user): ?>
                        <?php if ($user['role'] === 'sdr'): ?>
                            <option value="<?= $user['id'] ?>" <?= ($filters['sdr_id'] ?? '') == $user['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($user['full_name'] ?: $user['username']) ?>
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label for="duplicate_status" class="form-label">Status</label>
                <select class="form-select" id="duplicate_status" name="duplicate_status">
                    <option value="">All Status</option>
                    <option value="unique" <?= ($filters['duplicate_status'] ?? '') === 'unique' ? 'selected' : '' ?>>✅ Unique</option>
                    <option value="duplicate" <?= ($filters['duplicate_status'] ?? '') === 'duplicate' ? 'selected' : '' ?>>🔁 Duplicate</option>
                    <option value="incomplete" <?= ($filters['duplicate_status'] ?? '') === 'incomplete' ? 'selected' : '' ?>>⚠️ Incomplete</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="date_from" class="form-label">From Date</label>
                <input type="date" class="form-control" id="date_from" name="date_from" 
                       value="<?= htmlspecialchars($filters['date_from'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <label for="date_to" class="form-label">To Date</label>
                <input type="date" class="form-control" id="date_to" name="date_to" 
                       value="<?= htmlspecialchars($filters['date_to'] ?? '') ?>">
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-12">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-search me-2"></i>Search
                </button>
                <a href="index.php?action=leads" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-2"></i>Clear
                </a>
            </div>
        </div>
    </form>
</div>

<!-- Results -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Leads (<?= count($leads) ?> found)</h5>
        <div>
            <a href="index.php?action=export_csv" class="btn btn-sm btn-outline-success me-2">
                <i class="fas fa-download me-1"></i>Export CSV
            </a>
            <a href="index.php?action=export_excel" class="btn btn-sm btn-outline-success">
                <i class="fas fa-file-excel me-1"></i>Export Excel
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <?php if (!empty($leads)): ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Lead ID</th>
                            <th>Name</th>
                            <th>Company</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Website</th>
                            <th>SDR</th>
                            <th>Status</th>
                            <th>Date Added</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($leads as $lead): ?>
                            <tr>
                                <td>
                                    <a href="index.php?action=lead_view&id=<?= $lead['id'] ?>" class="text-decoration-none fw-bold">
                                        <?= htmlspecialchars($lead['lead_id']) ?>
                                    </a>
                                </td>
                                <td><?= htmlspecialchars($lead['name'] ?: 'N/A') ?></td>
                                <td><?= htmlspecialchars($lead['company'] ?: 'N/A') ?></td>
                                <td>
                                    <?php if ($lead['email']): ?>
                                        <a href="mailto:<?= htmlspecialchars($lead['email']) ?>" class="text-decoration-none">
                                            <?= htmlspecialchars($lead['email']) ?>
                                        </a>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($lead['phone']): ?>
                                        <a href="tel:<?= htmlspecialchars($lead['phone']) ?>" class="text-decoration-none">
                                            <?= htmlspecialchars($lead['phone']) ?>
                                        </a>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
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
                                <td><?= htmlspecialchars($lead['sdr_name'] ?: 'N/A') ?></td>
                                <td>
                                    <?php
                                    $statusClass = match($lead['duplicate_status']) {
                                        'unique' => 'status-unique',
                                        'duplicate' => 'status-duplicate',
                                        'incomplete' => 'status-incomplete',
                                        default => 'status-incomplete'
                                    };
                                    $statusIcon = match($lead['duplicate_status']) {
                                        'unique' => '✅',
                                        'duplicate' => '🔁',
                                        'incomplete' => '⚠️',
                                        default => '⚠️'
                                    };
                                    ?>
                                    <span class="status-badge <?= $statusClass ?>">
                                        <?= $statusIcon ?> <?= ucfirst($lead['duplicate_status']) ?>
                                    </span>
                                </td>
                                <td><?= date('M j, Y', strtotime($lead['created_at'])) ?></td>
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
                                        <a href="index.php?action=lead_delete&id=<?= $lead['id'] ?>" 
                                           class="btn btn-outline-danger btn-delete" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No leads found</h5>
                <p class="text-muted">Try adjusting your search criteria or add a new lead.</p>
                <a href="index.php?action=lead_add" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Add New Lead
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>