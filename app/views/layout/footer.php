    <?php if (auth_user()): ?>
            </main>
        </div>
    </div>
    <?php endif; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script>
        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });
        });

        // Confirm delete actions
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('btn-delete') || e.target.closest('.btn-delete')) {
                if (!confirm('Are you sure you want to delete this item?')) {
                    e.preventDefault();
                }
            }
        });

        // Form validation
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('.needs-validation');
            forms.forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                });
            });
        });

        // Meeting status updates (AJAX)
        document.addEventListener('DOMContentLoaded', function () {
            const statusSelects = document.querySelectorAll('.meeting-status-select');
            if (!statusSelects.length) {
                return;
            }

            statusSelects.forEach(function (select) {
                select.addEventListener('change', function () {
                    const meetingId = this.getAttribute('data-id');
                    const newStatus = this.value;
                    const row = this.closest('tr');
                    const spinner = row ? row.querySelector('.status-spinner') : null;
                    const feedback = row ? row.querySelector('.status-feedback') : null;

                    if (spinner) {
                        spinner.classList.remove('d-none');
                    }
                    if (feedback) {
                        feedback.classList.add('d-none');
                        feedback.textContent = '';
                    }

                    const formData = new FormData();
                    formData.append('meeting_id', meetingId);
                    formData.append('status', newStatus);

                    fetch('index.php?action=meeting_update_status', {
                        method: 'POST',
                        credentials: 'same-origin',
                        body: formData
                    })
                        .then(function (response) {
                            return response.json();
                        })
                        .then(function (data) {
                            if (spinner) {
                                spinner.classList.add('d-none');
                            }
                            if (!feedback) {
                                return;
                            }

                            if (data && data.success) {
                                feedback.textContent = 'Status updated';
                                feedback.classList.remove('text-danger');
                                feedback.classList.add('text-success');
                            } else {
                                feedback.textContent = data.message || 'Failed to update status';
                                feedback.classList.remove('text-success');
                                feedback.classList.add('text-danger');
                            }
                            feedback.classList.remove('d-none');

                            setTimeout(function () {
                                feedback.classList.add('d-none');
                            }, 3000);
                        })
                        .catch(function () {
                            if (spinner) {
                                spinner.classList.add('d-none');
                            }
                            if (feedback) {
                                feedback.textContent = 'Unexpected error. Please retry.';
                                feedback.classList.remove('text-success');
                                feedback.classList.add('text-danger');
                                feedback.classList.remove('d-none');
                            }
                        });
                });
            });
        });
    </script>
</body>
</html>