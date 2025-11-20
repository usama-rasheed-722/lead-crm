<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1"><i class="fas fa-handshake me-2"></i>Client Meetings</h2>
        <p class="text-muted mb-0">Manage meeting requests submitted through the Alifcode booking portal.</p>
    </div>
    <div>
        <a href="<?= base_url('index.php?action=meeting_portal') ?>" class="btn btn-outline-primary" target="_blank">
            <i class="fas fa-external-link-alt me-2"></i>Open Booking Portal
        </a>
    </div>
</div>

<?php if (!empty($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_GET['success']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
<?php if (!empty($_GET['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_GET['error']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-body">
        <form class="row g-3 align-items-end" method="GET" action="<?= base_url('index.php') ?>">
            <input type="hidden" name="action" value="meeting_clients">
            <div class="col-sm-6 col-md-3">
                <label for="search" class="form-label">Search</label>
                <input type="text" class="form-control" id="search" name="search" value="<?= htmlspecialchars($filters['search'] ?? '') ?>" placeholder="Name, email, or company">
            </div>
            <div class="col-sm-6 col-md-2">
                <label for="date_from" class="form-label">From</label>
                <input type="date" class="form-control" id="date_from" name="date_from" value="<?= htmlspecialchars($filters['date_from'] ?? '') ?>">
            </div>
            <div class="col-sm-6 col-md-2">
                <label for="date_to" class="form-label">To</label>
                <input type="date" class="form-control" id="date_to" name="date_to" value="<?= htmlspecialchars($filters['date_to'] ?? '') ?>">
            </div>
            <div class="col-sm-6 col-md-2">
                <label for="timezone" class="form-label">Timezone</label>
                <select class="form-select" id="timezone" name="timezone">
                    <option value="">All</option>
                    <?php foreach (($timezones ?? []) as $tz): ?>
                        <option value="<?= htmlspecialchars($tz) ?>" <?= (($filters['timezone'] ?? '') === $tz) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($tz) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-sm-6 col-md-2">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">All</option>
                    <?php foreach (($statuses ?? []) as $key => $label): ?>
                        <option value="<?= htmlspecialchars($key) ?>" <?= (($filters['status'] ?? '') === $key) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-sm-6 col-md-1">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter me-1"></i>Filter
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 meetings-table">
                <thead class="table-light">
                    <?php $serverTimezone = date_default_timezone_get(); ?>
                    <tr>
                        <th>Client</th>
                        <th>Company</th>
                        <th>Meeting Time</th>
                        <th>Local Time</th>
                        <th>Agenda</th>
                        <th>Feedback</th>
                        <th>Notes</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($meetings)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                <i class="fas fa-info-circle me-2"></i>No meeting requests found with the current filters.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($meetings as $meeting): ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?= htmlspecialchars($meeting['client_name']) ?></div>
                                    <div class="text-muted small">
                                        <i class="fas fa-envelope me-1"></i><?= htmlspecialchars($meeting['client_email']) ?><br>
                                        <?php if (!empty($meeting['client_phone'])): ?>
                                            <i class="fas fa-phone me-1"></i><?= htmlspecialchars($meeting['client_phone']) ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold"><?= htmlspecialchars($meeting['company_name']) ?></div>
                                    <div class="text-muted small"><?= htmlspecialchars($meeting['business_model']) ?></div>
                                </td>
                                <td>
                                    <div class="fw-semibold">
                                        <?= htmlspecialchars(date('M d, Y', strtotime($meeting['preferred_date']))) ?>
                                        at <?= htmlspecialchars(date('h:i A', strtotime($meeting['preferred_time']))) ?>
                                    </div>
                                    <div class="badge bg-light text-dark mt-1"><?= htmlspecialchars($meeting['timezone']) ?></div>
                                </td>
                                <td>
                                    <?php
                                        $utcTimestamp = null;
                                        try {
                                            if (!empty($meeting['preferred_datetime_utc'])) {
                                                $utcTime = new DateTime($meeting['preferred_datetime_utc'], new DateTimeZone('UTC'));
                                                $utcTimestamp = $utcTime->format('c'); // ISO 8601 format
                                            } else {
                                                $clientTime = new DateTime($meeting['preferred_date'] . ' ' . $meeting['preferred_time'], new DateTimeZone($meeting['timezone']));
                                                $clientTime->setTimezone(new DateTimeZone('UTC'));
                                                $utcTimestamp = $clientTime->format('c'); // ISO 8601 format
                                            }
                                        } catch (Exception $e) {
                                            $utcTimestamp = null;
                                        }
                                    ?>
                                    <div class="fw-semibold local-time-display" data-utc-time="<?= $utcTimestamp ? htmlspecialchars($utcTimestamp) : '' ?>">—</div>
                                    <div class="text-muted small">Your timezone</div>
                                </td>
                                <td class="text-muted small">
                                    <?= nl2br(htmlspecialchars($meeting['meeting_agenda'])) ?>
                                </td>
                                <td class="text-muted small">
                                    <?= $meeting['client_feedback'] ? nl2br(htmlspecialchars($meeting['client_feedback'])) : '<span class="text-muted">—</span>' ?>
                                </td>
                                <td class="text-muted small">
                                    <?= $meeting['client_notes'] ? nl2br(htmlspecialchars($meeting['client_notes'])) : '<span class="text-muted">—</span>' ?>
                                    <?php if (!empty($meeting['admin_notes'])): ?>
                                        <div class="mt-2">
                                            <span class="badge bg-primary-subtle text-primary">Admin</span>
                                            <div><?= nl2br(htmlspecialchars($meeting['admin_notes'])) ?></div>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <select class="form-select form-select-sm meeting-status-select"
                                            data-id="<?= (int) $meeting['id'] ?>">
                                        <?php foreach (($statuses ?? []) as $key => $label): ?>
                                            <option value="<?= htmlspecialchars($key) ?>"
                                                <?= ($meeting['status'] === $key) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($label) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="status-spinner text-muted small mt-1 d-none">
                                        <i class="fas fa-spinner fa-spin me-1"></i>Updating...
                                    </div>
                                    <div class="status-feedback text-muted small mt-1 d-none"></div>
                                </td>
                                <td class="text-muted small">
                                    <?= htmlspecialchars(date('M d, Y H:i', strtotime($meeting['created_at']))) ?>
                                </td>
                                <td class="text-end">
                                    <button type="button"
                                            class="btn btn-sm btn-outline-primary meeting-notes-btn"
                                            data-bs-toggle="modal"
                                            data-bs-target="#meetingNotesModal"
                                            data-id="<?= (int) $meeting['id'] ?>"
                                            data-name="<?= htmlspecialchars($meeting['client_name']) ?>"
                                            data-notes="<?= htmlspecialchars($meeting['admin_notes'] ?? '') ?>">
                                        <i class="fas fa-sticky-note me-1"></i>Update Notes
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if (($total_pages ?? 1) > 1): ?>
    <div class="card-footer d-flex justify-content-between align-items-center">
        <div class="text-muted small">
            Showing <?= ($page - 1) * $perPage + 1 ?> to <?= min($page * $perPage, $total) ?> of <?= $total ?> meetings
        </div>
        <nav>
            <ul class="pagination mb-0">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= $page === $i ? 'active' : '' ?>">
                        <a class="page-link"
                           href="<?= base_url('index.php?' . http_build_query(array_merge($_GET, ['page' => $i]))) ?>">
                            <?= $i ?>
                        </a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>

<!-- Notes Modal -->
<div class="modal fade" id="meetingNotesModal" tabindex="-1" aria-labelledby="meetingNotesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" method="POST" action="<?= base_url('index.php?action=meeting_update_notes') ?>">
            <div class="modal-header">
                <h5 class="modal-title" id="meetingNotesModalLabel">Update Meeting Notes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="meeting_id" id="meeting_notes_id">
                <div class="mb-3">
                    <label class="form-label text-muted">Client</label>
                    <div class="fw-semibold" id="meeting_notes_client">—</div>
                </div>
                <div class="mb-3">
                    <label for="admin_notes" class="form-label">Internal Notes</label>
                    <textarea class="form-control" id="admin_notes" name="admin_notes" rows="5" placeholder="Add internal notes or next steps for this meeting."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Notes</button>
            </div>
        </form>
    </div>
</div>

<script>
    /**
     * Converts a UTC datetime string to the client's local timezone
     * @param {string} utcTimeString - ISO 8601 formatted UTC datetime string
     * @returns {string} Formatted local time string (e.g., "Jan 15, 2024 at 14:30")
     */
    function convertToLocalTime(utcTimeString) {
        if (!utcTimeString) {
            return '—';
        }
        
        try {
            const utcDate = new Date(utcTimeString);
            
            // Check if date is valid
            if (isNaN(utcDate.getTime())) {
                return '—';
            }
            
            // Format the date in local timezone
            const options = { 
                month: 'short', 
                day: 'numeric', 
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                hour12: true
            };
            
            const localDateString = utcDate.toLocaleDateString('en-US', options);
            // Format: "Jan 15, 2024, 14:30" -> "Jan 15, 2024 at 14:30"
            return localDateString.replace(',', ' at');
        } catch (e) {
            console.error('Error converting time:', e);
            return '—';
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Convert all UTC times to local timezone
        const localTimeDisplays = document.querySelectorAll('.local-time-display');
        localTimeDisplays.forEach(function(element) {
            const utcTime = element.getAttribute('data-utc-time');
            if (utcTime) {
                element.textContent = convertToLocalTime(utcTime);
            }
        });

        // Notes modal handling
        const notesModal = document.getElementById('meetingNotesModal');
        if (!notesModal) {
            return;
        }

        notesModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            if (!button) {
                return;
            }

            const meetingId = button.getAttribute('data-id');
            const meetingName = button.getAttribute('data-name');
            const meetingNotes = button.getAttribute('data-notes');

            const idInput = notesModal.querySelector('#meeting_notes_id');
            const nameContainer = notesModal.querySelector('#meeting_notes_client');
            const notesTextarea = notesModal.querySelector('#admin_notes');

            if (idInput) {
                idInput.value = meetingId || '';
            }
            if (nameContainer) {
                nameContainer.textContent = meetingName || '—';
            }
            if (notesTextarea) {
                notesTextarea.value = meetingNotes || '';
            }
        });
    });
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>

