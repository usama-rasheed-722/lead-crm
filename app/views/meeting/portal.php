<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Book a Meeting') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= base_url('public/assets/css/app.css') ?>" rel="stylesheet">
</head>
<body class="meeting-portal-body">
    <div class="meeting-portal-wrapper ">
        <div class="meeting-card shadow-lg container-fluid p-5">
            <?php if (!empty($success)): ?>
                <div class="alert alert-success mb-4" role="alert">
                    <strong>Thank you!</strong> Your meeting request has been received. Our team will reach out shortly with the confirmation details.
                </div>
            <?php elseif (!empty($error)): ?>
                <div class="alert alert-danger mb-4" role="alert">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <div class="meeting-steps">
                <div class="meeting-step meeting-step-branding active" data-step="branding">
                    <div class="text-center">
                        <div class="brand-logo-wrapper mb-4">
                            <img src="<?= base_url('public/assets/images/alifcode-logo.svg') ?>" alt="Alifcode" class="brand-logo">
                        </div>
                        <h1 class="mb-3">Let’s Build Something Extraordinary</h1>
                        <p class="lead text-muted">
                            Book a meeting with the Alifcode team to explore how our solutions can empower your business.
                        </p>
                    </div>
                </div>

                <div class="meeting-step meeting-step-form" data-step="form">
                    <h2 class="mb-4">Book Your Meeting</h2>
                    <form class="row g-3 needs-validation meeting-form" method="POST" action="<?= base_url('index.php?action=meeting_submit') ?>" novalidate>
                        <div class="col-md-6">
                            <label for="client_name" class="form-label">Full Name *</label>
                            <input type="text" class="form-control" id="client_name" name="client_name" required>
                            <div class="invalid-feedback">Please enter your name.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="client_email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="client_email" name="client_email" required>
                            <div class="invalid-feedback">Please enter a valid email.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="client_phone" class="form-label">Phone</label>
                            <input type="tel" class="form-control" id="client_phone" name="client_phone" placeholder="+1 (555) 123-4567">
                        </div>
                        <div class="col-md-6">
                            <label for="company_name" class="form-label">Company Name *</label>
                            <input type="text" class="form-control" id="company_name" name="company_name" required>
                            <div class="invalid-feedback">Please enter your company.</div>
                        </div>
                        <div class="col-12">
                            <label for="business_model" class="form-label">Business Model *</label>
                            <textarea class="form-control" id="business_model" name="business_model" rows="3" required></textarea>
                            <div class="invalid-feedback">Please describe your business model.</div>
                        </div>
                        <div class="col-12">
                            <label for="meeting_agenda" class="form-label">Meeting Agenda *</label>
                            <textarea class="form-control" id="meeting_agenda" name="meeting_agenda" rows="3" required></textarea>
                            <div class="invalid-feedback">Please outline your agenda.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="preferred_date" class="form-label">Preferred Date *</label>
                            <input type="date" class="form-control" id="preferred_date" name="preferred_date" required>
                            <div class="invalid-feedback">Select a preferred date.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="preferred_time" class="form-label">Preferred Time *</label>
                            <input type="time" class="form-control" id="preferred_time" name="preferred_time" required>
                            <div class="invalid-feedback">Select a preferred time.</div>
                        </div>
                        <div class="col-12">
                            <label for="timezone" class="form-label">Timezone *</label>
                            <select class="form-select" id="timezone" name="timezone" required>
                                <option value="" selected disabled>Select your timezone</option>
                                <?php foreach ($timezones as $tz): ?>
                                    <option value="<?= htmlspecialchars($tz) ?>"><?= htmlspecialchars($tz) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Choose a timezone.</div>
                        </div>
                        <div class="col-12">
                            <label for="client_feedback" class="form-label">What outcome do you expect?</label>
                            <textarea class="form-control" id="client_feedback" name="client_feedback" rows="3" placeholder="Share the results you want to achieve from this meeting."></textarea>
                        </div>
                        <div class="col-12">
                            <label for="client_notes" class="form-label">Additional Notes</label>
                            <textarea class="form-control" id="client_notes" name="client_notes" rows="3" placeholder="Anything else we should know?"></textarea>
                        </div>
                        <div class="col-12 text-end mt-3">
                            <button type="submit" class="btn btn-primary btn-lg px-5">
                                Book Meeting
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const nextBtn = document.querySelector('.meeting-next-btn');
            const brandingStep = document.querySelector('.meeting-step-branding');
            const formStep = document.querySelector('.meeting-step-form');
            const dateInput = document.getElementById('preferred_date');

            if (dateInput) {
                const today = new Date().toISOString().split('T')[0];
                dateInput.setAttribute('min', today);
            }

            if (nextBtn && brandingStep && formStep) {
                nextBtn.addEventListener('click', function () {
                    brandingStep.classList.remove('active');
                    brandingStep.classList.add('done');
                    formStep.classList.add('active');
                });
            }
        });
    </script>
</body>
</html>

