<?php
// app/controllers/MeetingController.php

class MeetingController extends Controller
{
    protected $meetingModel;

    public function __construct()
    {
        parent::__construct();
        $this->meetingModel = new MeetingModel();
    }

    public function portal()
    {
        $timezones = DateTimeZone::listIdentifiers();
        sort($timezones);

        $this->view('meeting/portal', [
            'title'     => 'Book a Meeting',
            'timezones' => $timezones,
            'success'   => ($_GET['success'] ?? '') === '1',
            'error'     => $_GET['error'] ?? '',
        ]);
    }

    public function submit()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('index.php?action=meeting_portal');
        }

        $payload = [
            'client_name'     => trim($_POST['client_name'] ?? ''),
            'client_email'    => trim($_POST['client_email'] ?? ''),
            'client_phone'    => trim($_POST['client_phone'] ?? ''),
            'company_name'    => trim($_POST['company_name'] ?? ''),
            'business_model'  => trim($_POST['business_model'] ?? ''),
            'meeting_agenda'  => trim($_POST['meeting_agenda'] ?? ''),
            'preferred_date'  => trim($_POST['preferred_date'] ?? ''),
            'preferred_time'  => trim($_POST['preferred_time'] ?? ''),
            'timezone'        => trim($_POST['timezone'] ?? ''),
            'client_feedback' => trim($_POST['client_feedback'] ?? ''),
            'client_notes'    => trim($_POST['client_notes'] ?? ''),
        ];

        $error = $this->validateSubmission($payload);
        if ($error) {
            $this->redirect('index.php?action=meeting_portal&error=' . urlencode($error));
        }

        if ($this->meetingModel->hasConflict($payload['preferred_date'], $payload['preferred_time'], $payload['timezone'])) {
            $message = 'The selected time slot is no longer available. Please choose another time.';
            $this->redirect('index.php?action=meeting_portal&error=' . urlencode($message));
        }

        $payload['preferred_datetime_utc'] = $this->buildUtcDateTime($payload);

        try {
            $this->meetingModel->create($payload);
            $this->redirect('index.php?action=meeting_portal&success=1');
        } catch (Exception $e) {
            $this->redirect('index.php?action=meeting_portal&error=' . urlencode('Unable to book meeting right now. Please try again.'));
        }
    }

    public function index()
    {
        require_role(['admin', 'manager']);

        $filters = [
            'date_from' => $_GET['date_from'] ?? '',
            'date_to'   => $_GET['date_to'] ?? '',
            'timezone'  => $_GET['timezone'] ?? '',
            'status'    => $_GET['status'] ?? '',
            'search'    => $_GET['search'] ?? '',
        ];

        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 25;
        $offset  = ($page - 1) * $perPage;

        $meetings   = $this->meetingModel->getMeetings($filters, $perPage, $offset);
        $total      = $this->meetingModel->countMeetings($filters);
        $totalPages = (int) ceil($total / $perPage);
        $timezones  = $this->meetingModel->getDistinctTimezones();

        $this->view('meeting/index', [
            'title'       => 'Client Meetings',
            'meetings'    => $meetings,
            'filters'     => $filters,
            'page'        => $page,
            'perPage'     => $perPage,
            'total'       => $total,
            'total_pages' => $totalPages,
            'timezones'   => $timezones,
            'statuses'    => ['new' => 'New', 'scheduled' => 'Scheduled', 'completed' => 'Completed', 'cancelled' => 'Cancelled'],
        ]);
    }

    public function updateNotes()
    {
        require_role(['admin', 'manager']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('index.php?action=meeting_clients');
        }

        $meetingId = (int) ($_POST['meeting_id'] ?? 0);
        $notes     = trim($_POST['admin_notes'] ?? '');

        if ($meetingId <= 0) {
            $this->redirect('index.php?action=meeting_clients&error=' . urlencode('Meeting not found.'));
        }

        try {
            $this->meetingModel->updateAdminNotes($meetingId, $notes);
            $this->redirect('index.php?action=meeting_clients&success=' . urlencode('Notes updated.'));
        } catch (Exception $e) {
            $this->redirect('index.php?action=meeting_clients&error=' . urlencode('Unable to update notes right now.'));
        }
    }

    public function updateStatus()
    {
        require_role(['admin', 'manager']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json([
                'success' => false,
                'message' => 'Invalid request method.',
            ]);
        }

        $meetingId = $_POST['meeting_id'] ?? null;
        $meetingId = is_numeric($meetingId) ? (int) $meetingId : 0;
        $status    = trim($_POST['status'] ?? '');
        $status = strtolower($status);

        if ($meetingId <= 0) {
            $this->json([
                'success' => false,
                'message' => 'Invalid meeting reference.',
            ]);
        }

        if ($status === '') {
            $this->json([
                'success' => false,
                'message' => 'Status value is required.',
            ]);
        }

        $allowed = ['new', 'scheduled', 'completed', 'cancelled'];
        if (!in_array($status, $allowed, true)) {
            $this->json([
                'success' => false,
                'message' => 'Invalid status value.',
            ]);
        }

        try {
            $this->meetingModel->updateStatus($meetingId, $status);
            $this->json([
                'success' => true,
                'status' => $status,
            ]);
        } catch (Exception $e) {
            $this->json([
                'success' => false,
                'message' => 'Unable to update status.',
            ]);
        }
    }

    private function validateSubmission(array &$payload)
    {
        $required = ['client_name', 'client_email', 'company_name', 'business_model', 'meeting_agenda', 'preferred_date', 'preferred_time', 'timezone'];
        foreach ($required as $field) {
            if (empty($payload[$field])) {
                return 'Please fill out all required fields.';
            }
        }

        if (!filter_var($payload['client_email'], FILTER_VALIDATE_EMAIL)) {
            return 'Please provide a valid email address.';
        }

        if (!$this->isValidDate($payload['preferred_date'])) {
            return 'Please select a valid meeting date.';
        }

        if (!$this->isValidTime($payload['preferred_time'])) {
            return 'Please select a valid meeting time.';
        }

        $validTimezones = DateTimeZone::listIdentifiers();
        if (!in_array($payload['timezone'], $validTimezones, true)) {
            return 'Please choose a valid timezone.';
        }

        $payload['status'] = 'new';

        return null;
    }

    private function buildUtcDateTime($payload)
    {
        $payload = (array) $payload;

        try {
            $meetingTz = new DateTimeZone($payload['timezone']);
        } catch (Exception $e) {
            $meetingTz = new DateTimeZone('UTC');
        }

        $combined = new DateTime($payload['preferred_date'] . ' ' . $payload['preferred_time'], $meetingTz);
        $combined->setTimezone(new DateTimeZone('UTC'));

        return $combined->format('Y-m-d H:i:s');
    }

    private function isValidDate($date)
    {
        [$year, $month, $day] = array_pad(explode('-', $date), 3, null);
        return checkdate((int) $month, (int) $day, (int) $year);
    }

    private function isValidTime($time)
    {
        return (bool) preg_match('/^(2[0-3]|[01]\d):[0-5]\d$/', $time);
    }
}

