<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-users me-2"></i>User Management</h2>
    <a href="index.php?action=user_add" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Add User
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

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">All Users (<?= count($users) ?>)</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Full Name</th>
                        <th>Role</th>
                        <th>SDR ID</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($users)): ?>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= htmlspecialchars($user['id']) ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($user['username']) ?></strong>
                                </td>
                                <td>
                                    <a href="mailto:<?= htmlspecialchars($user['email']) ?>" class="text-decoration-none">
                                        <?= htmlspecialchars($user['email']) ?>
                                    </a>
                                </td>
                                <td><?= htmlspecialchars($user['full_name'] ?: 'N/A') ?></td>
                                <td>
                                    <?php
                                    $roleClass = 'bg-secondary';
                                    if ($user['role'] === 'admin') {
                                        $roleClass = 'bg-danger';
                                    } elseif ($user['role'] === 'manager') {
                                        $roleClass = 'bg-warning';
                                    } elseif ($user['role'] === 'sdr') {
                                        $roleClass = 'bg-info';
                                    }
                                    ?>
                                    <span class="badge <?= $roleClass ?>">
                                        <?= ucfirst($user['role']) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($user['sdr_id'] ?? '—') ?></td>
                                <td><?= date('M j, Y', strtotime($user['created_at'])) ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="index.php?action=user_edit&id=<?= $user['id'] ?>" 
                                           class="btn btn-outline-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="index.php?action=user_delete&id=<?= $user['id'] ?>" 
                                           class="btn btn-outline-danger btn-delete" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No users found.</p>
                                <a href="index.php?action=user_add" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Add First User
                                </a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>