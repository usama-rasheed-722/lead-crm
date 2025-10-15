<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>
        <i class="fas fa-<?= $action === 'edit' ? 'edit' : 'plus' ?> me-2"></i>
        <?= $action === 'edit' ? 'Edit User' : 'Add New User' ?>
    </h2>
    <a href="index.php?action=users" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Back to Users
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="index.php?action=<?= $action === 'edit' ? 'user_update&id=' . $user['id'] : 'user_store' ?>" 
                      class="needs-validation" novalidate>
                    
                    <div class="mb-3">
                        <label for="username" class="form-label">Username *</label>
                        <input type="text" class="form-control" id="username" name="username" 
                               value="<?= htmlspecialchars($user['username'] ?? '') ?>" required>
                        <div class="invalid-feedback">
                            Please provide a username.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email *</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                        <div class="invalid-feedback">
                            Please provide a valid email.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="full_name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="full_name" name="full_name" 
                               value="<?= htmlspecialchars($user['full_name'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label for="role" class="form-label">Role *</label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="sdr" <?= ($user['role'] ?? '') === 'sdr' ? 'selected' : '' ?>>SDR</option>
                            <option value="manager" <?= ($user['role'] ?? '') === 'manager' ? 'selected' : '' ?>>Manager</option>
                            <option value="admin" <?= ($user['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                        </select>
                        <div class="form-text">
                            <strong>SDR:</strong> Can view/add/edit their own leads<br>
                            <strong>Manager:</strong> Can view all leads, analytics, and team performance<br>
                            <strong>Admin:</strong> Full control over users, leads, and settings
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="sdr_id" class="form-label">SDR ID</label>
                        <input type="number" class="form-control" id="sdr_id" name="sdr_id"
                               value="<?= htmlspecialchars($user['sdr_id'] ?? '') ?>" placeholder="Enter SDR code/number">
                        <div class="form-text">Used to generate lead IDs and filter lead access.</div>
                    </div>

                    <?php if ($action === 'create'): ?>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password *</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <div class="invalid-feedback">
                            Please provide a password.
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="d-flex justify-content-between">
                        <a href="index.php?action=users" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>
                            <?= $action === 'edit' ? 'Update User' : 'Create User' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
