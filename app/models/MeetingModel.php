<?php
// app/models/MeetingModel.php

class MeetingModel extends BaseModel
{
    private $table = 'client_meetings';

    public function create(array $data)
    {
        $sql = "
            INSERT INTO {$this->table}
            (client_name, client_email, client_phone, company_name, business_model, meeting_agenda, preferred_date, preferred_time, timezone, preferred_datetime_utc, client_feedback, client_notes, status)
            VALUES
            (:client_name, :client_email, :client_phone, :company_name, :business_model, :meeting_agenda, :preferred_date, :preferred_time, :timezone, :preferred_datetime_utc, :client_feedback, :client_notes, :status)
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':client_name'     => $data['client_name'],
            ':client_email'    => $data['client_email'],
            ':client_phone'    => $data['client_phone'] ?: null,
            ':company_name'    => $data['company_name'],
            ':business_model'  => $data['business_model'],
            ':meeting_agenda'  => $data['meeting_agenda'],
            ':preferred_date'  => $data['preferred_date'],
            ':preferred_time'  => $data['preferred_time'],
            ':timezone'        => $data['timezone'],
            ':preferred_datetime_utc' => $data['preferred_datetime_utc'],
            ':client_feedback' => $data['client_feedback'] ?: null,
            ':client_notes'    => $data['client_notes'] ?: null,
            ':status'          => $data['status'] ?? 'new',
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function getMeetings(array $filters = [], $limit = 25, $offset = 0)
    {
        [$whereSql, $params] = $this->buildFilters($filters);

        $sql = "
            SELECT *
            FROM {$this->table}
            {$whereSql}
            ORDER BY preferred_date ASC, preferred_time ASC
            LIMIT ? OFFSET ?
        ";

        $stmt = $this->pdo->prepare($sql);
        $paramIndex = 1;
        foreach ($params as $value) {
            $stmt->bindValue($paramIndex++, $value);
        }
        $stmt->bindValue($paramIndex++, (int) $limit, PDO::PARAM_INT);
        $stmt->bindValue($paramIndex, (int) $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function countMeetings(array $filters = [])
    {
        [$whereSql, $params] = $this->buildFilters($filters);

        $sql = "SELECT COUNT(*) as total FROM {$this->table} {$whereSql}";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        $row = $stmt->fetch();
        return (int) ($row['total'] ?? 0);
    }

    public function updateAdminNotes($meetingId, $notes)
    {
        $sql = "UPDATE {$this->table} SET admin_notes = :admin_notes WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':admin_notes' => $notes,
            ':id' => $meetingId,
        ]);
    }

    public function updateStatus($meetingId, $status)
    {
        $sql = "UPDATE {$this->table} SET status = :status WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            ':status' => $status,
            ':id'     => $meetingId,
        ]);
    }

    public function getMeeting($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch();

        return $result ?: null;
    }

    public function getDistinctTimezones()
    {
        $sql = "SELECT DISTINCT timezone FROM {$this->table} WHERE timezone IS NOT NULL AND timezone <> '' ORDER BY timezone ASC";
        $stmt = $this->pdo->query($sql);

        return array_column($stmt->fetchAll(), 'timezone');
    }

    public function hasConflict($date, $time, $timezone, $windowMinutes = 60)
    {
        if (empty($date) || empty($time) || empty($timezone)) {
            return false;
        }

        try {
            $requestedTz = new DateTimeZone($timezone);
        } catch (Exception $e) {
            return false;
        }

        $requested = new DateTime("{$date} {$time}", $requestedTz);
        $startWindow = clone $requested;
        $endWindow = clone $requested;

        $intervalSpec = sprintf('PT%dM', (int) $windowMinutes);
        $interval = new DateInterval($intervalSpec);

        $startWindow->sub($interval);
        $endWindow->add($interval);

        $utcTz = new DateTimeZone('UTC');
        $startUtc = $startWindow->setTimezone($utcTz)->format('Y-m-d H:i:s');
        $endUtc = $endWindow->setTimezone($utcTz)->format('Y-m-d H:i:s');

        $sql = "
            SELECT COUNT(*) AS total
            FROM {$this->table}
            WHERE preferred_datetime_utc BETWEEN :start AND :end
              AND status <> 'cancelled'
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':start' => $startUtc,
            ':end'   => $endUtc,
        ]);

        $row = $stmt->fetch();
        return ($row['total'] ?? 0) > 0;
    }

    private function buildFilters(array $filters)
    {
        $where = [];
        $params = [];

        if (!empty($filters['date_from'])) {
            $where[] = 'preferred_date >= ?';
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where[] = 'preferred_date <= ?';
            $params[] = $filters['date_to'];
        }

        if (!empty($filters['timezone'])) {
            $where[] = 'timezone = ?';
            $params[] = $filters['timezone'];
        }

        if (!empty($filters['status'])) {
            $where[] = 'status = ?';
            $params[] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $where[] = '(client_name LIKE ? OR client_email LIKE ? OR company_name LIKE ?)';
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        return [$whereSql, $params];
    }
}

