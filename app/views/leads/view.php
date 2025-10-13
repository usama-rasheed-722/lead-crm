<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>
        <i class="fas fa-user me-2"></i>Lead Details
    </h2>
    <div>
        <?php if (empty($lead['lead_id']) || !preg_match('/^SDR\d+-\d+$/', $lead['lead_id'])): ?>
            <a href="index.php?action=generate_sdr&id=<?= $lead['id'] ?>" class="btn btn-success me-2">
                <i class="fas fa-hashtag me-2"></i>Generate SDR Number
            </a>
        <?php endif; ?>
        <a href="index.php?action=lead_edit&id=<?= $lead['id'] ?>" class="btn btn-outline-primary me-2">
            <i class="fas fa-edit me-2"></i>Edit Lead
        </a>
        <a href="index.php?action=leads" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Leads
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
    <!-- Lead Information -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Lead Information</h5>
                <span class="badge bg-<?= match($lead['duplicate_status']) { 'success' => 'success', 'warning' => 'warning', 'danger' => 'danger', default => 'secondary' } ?>">
                    <?php
                    $statusIcon = match($lead['duplicate_status']) {
                        'unique' => '✅',
                        'duplicate' => '🔁',
                        'incomplete' => '⚠️',
                        default => '❓'
                    };
                    echo $statusIcon . ' ' . ucfirst($lead['duplicate_status']);
                    ?>
                </span>
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
                                <td class="fw-bold">Job Title:</td>
                                <td><?= htmlspecialchars($lead['job_title'] ?: 'N/A') ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Company:</td>
                                <td><?= htmlspecialchars($lead['company'] ?: 'N/A') ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Email:</td>
                                <td>
                                    <?php if ($lead['email']): ?>
                                        <a href="mailto:<?= htmlspecialchars($lead['email']) ?>" class="text-decoration-none">
                                            <?= htmlspecialchars($lead['email']) ?>
                                        </a>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Phone:</td>
                                <td>
                                    <?php if ($lead['phone']): ?>
                                        <a href="tel:<?= htmlspecialchars($lead['phone']) ?>" class="text-decoration-none">
                                            <?= htmlspecialchars($lead['phone']) ?>
                                        </a>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="fw-bold">LinkedIn:</td>
                                <td>
                                    <?php if ($lead['linkedin']): ?>
                                        <a href="<?= htmlspecialchars($lead['linkedin']) ?>" target="_blank" class="text-decoration-none">
                                            <i class="fab fa-linkedin me-1"></i>
                                            View Profile
                                        </a>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Website:</td>
                                <td>
                                    <?php if ($lead['website']): ?>
                                        <a href="<?= htmlspecialchars($lead['website']) ?>" target="_blank" class="text-decoration-none">
                                            <i class="fas fa-external-link-alt me-1"></i>
                                            <?= htmlspecialchars($lead['website']) ?>
                                        </a>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Clutch:</td>
                                <td>
                                    <?php if ($lead['clutch']): ?>
                                        <a href="<?= htmlspecialchars($lead['clutch']) ?>" target="_blank" class="text-decoration-none">
                                            <i class="fas fa-external-link-alt me-1"></i>
                                            View Profile
                                        </a>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Industry:</td>
                                <td><?= htmlspecialchars($lead['industry'] ?: 'N/A') ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Lead Source:</td>
                                <td><?= htmlspecialchars($lead['lead_source'] ?: 'N/A') ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Tier:</td>
                                <td><?= htmlspecialchars($lead['tier'] ?: 'N/A') ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Lead Status:</td>
                                <td><?= htmlspecialchars($lead['lead_status'] ?: 'N/A') ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Instagram:</td>
                                <td><?= htmlspecialchars($lead['insta'] ?: 'N/A') ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Social Profile:</td>
                                <td><?= htmlspecialchars($lead['social_profile'] ?: 'N/A') ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Address:</td>
                                <td><?= htmlspecialchars($lead['address'] ?: 'N/A') ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Country:</td>
                                <td><?= htmlspecialchars($lead['country'] ?: 'N/A') ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Whatsapp:</td>
                                <td><?= htmlspecialchars($lead['whatsapp'] ?: 'N/A') ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Next Step:</td>
                                <td><?= htmlspecialchars($lead['next_step'] ?: 'N/A') ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Other:</td>
                                <td><?= htmlspecialchars($lead['other'] ?: 'N/A') ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Status:</td>
                                <td><?= htmlspecialchars($lead['status'] ?: 'N/A') ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">SDR:</td>
                                <td><?= htmlspecialchars($lead['sdr_name'] ?: 'N/A') ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Created:</td>
                                <td><?= date('M j, Y g:i A', strtotime($lead['created_at'])) ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Updated:</td>
                                <td><?= date('M j, Y g:i A', strtotime($lead['updated_at'])) ?></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <?php if ($lead['notes']): ?>
                <div class="mt-4">
                    <h6 class="fw-bold">Notes:</h6>
                    <div class="bg-light p-3 rounded">
                        <?= nl2br(htmlspecialchars($lead['notes'])) ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <?php if ($lead['email']): ?>
                        <a href="mailto:<?= htmlspecialchars($lead['email']) ?>" class="btn btn-outline-primary">
                            <i class="fas fa-envelope me-2"></i>Send Email
                        </a>
                    <?php endif; ?>
                    <?php if ($lead['phone']): ?>
                        <a href="tel:<?= htmlspecialchars($lead['phone']) ?>" class="btn btn-outline-success">
                            <i class="fas fa-phone me-2"></i>Call
                        </a>
                    <?php endif; ?>
                    <?php if ($lead['linkedin']): ?>
                        <a href="<?= htmlspecialchars($lead['linkedin']) ?>" target="_blank" class="btn btn-outline-info">
                            <i class="fab fa-linkedin me-2"></i>LinkedIn
                        </a>
                    <?php endif; ?>
                    <?php if ($lead['website']): ?>
                        <a href="<?= htmlspecialchars($lead['website']) ?>" target="_blank" class="btn btn-outline-secondary">
                            <i class="fas fa-globe me-2"></i>Website
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Add Note -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Add Note</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="index.php?action=notes_add">
                    <input type="hidden" name="lead_id" value="<?= $lead['id'] ?>">
                    <div class="mb-3">
                        <label for="note_type" class="form-label">Type</label>
                        <select class="form-select" id="note_type" name="type" required>
                            <option value="note">Note</option>
                            <option value="call">Call</option>
                            <option value="email">Email</option>
                            <option value="update">Update</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="note_content" class="form-label">Content</label>
                        <textarea class="form-control" id="note_content" name="content" rows="3" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-plus me-2"></i>Add Note
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Notes Section -->
<?php if (!empty($notes)): ?>
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Activity & Notes</h5>
            </div>
            <div class="card-body">
                <?php foreach ($notes as $note): ?>
                    <div class="note-item">
                        <div class="note-meta">
                            <i class="fas fa-<?= match($note['type']) { 'call' => 'phone', 'email' => 'envelope', 'update' => 'edit', default => 'sticky-note' } ?> me-1"></i>
                            <strong><?= ucfirst($note['type']) ?></strong> by 
                            <?= htmlspecialchars($note['full_name'] ?: $note['username']) ?> 
                            on <?= date('M j, Y g:i A', strtotime($note['created_at'])) ?>
                        </div>
                        <div><?= nl2br(htmlspecialchars($note['content'])) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../layout/footer.php'; ?>