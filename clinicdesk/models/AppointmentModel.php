<?php
require_once __DIR__ . '/BaseModel.php';

class AppointmentModel extends BaseModel {
    public function book(array $data): bool {
        $result = $this->execute(
            'INSERT INTO appointments (patient_id, doctor_id, appt_date, appt_time, reason)
             VALUES (?, ?, ?, ?, ?)',
            'iisss',
            [$data['patient_id'], $data['doctor_id'], $data['appt_date'], $data['appt_time'], $data['reason'] ?? null]
        );
        if ($result === false) return false;
        return $this->db->errno() === 0;
    }

    public function hasConflict(int $doctorId, string $date, string $time): bool {
        $result = $this->execute(
            'SELECT id FROM appointments
             WHERE doctor_id = ? AND appt_date = ? AND appt_time = ?
             AND status NOT IN ("cancelled")',
            'iss', [$doctorId, $date, $time]
        );
        return $result->num_rows > 0;
    }

    public function findById(int $id): ?array {
        $result = $this->execute(
            'SELECT a.*,
                    u_p.name AS patient_name, u_p.email AS patient_email,
                    u_d.name AS doctor_name,
                    s.name AS specialization_name,
                    d.id AS doctor_table_id
             FROM appointments a
             JOIN users u_p ON u_p.id = a.patient_id
             JOIN doctors d ON d.id = a.doctor_id
             JOIN users u_d ON u_d.id = d.user_id
             JOIN specializations s ON s.id = d.specialization_id
             WHERE a.id = ?',
            'i', [$id]
        );
        $row = $result->fetch_assoc();
        return $row ?: null;
    }

    private function buildWhere(array $filters): array {
        $conditions = [];
        $types      = '';
        $params     = [];

        if (!empty($filters['status'])) {
            $conditions[] = 'a.status = ?';
            $types .= 's';
            $params[] = $filters['status'];
        }
        if (!empty($filters['doctor_id'])) {
            $conditions[] = 'a.doctor_id = ?';
            $types .= 'i';
            $params[] = (int) $filters['doctor_id'];
        }
        if (!empty($filters['start_date'])) {
            $conditions[] = 'a.appt_date >= ?';
            $types .= 's';
            $params[] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $conditions[] = 'a.appt_date <= ?';
            $types .= 's';
            $params[] = $filters['end_date'];
        }
        if (!empty($filters['patient_name'])) {
            $conditions[] = 'u_p.name LIKE ?';
            $types .= 's';
            $params[] = '%' . $filters['patient_name'] . '%';
        }

        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
        return [$where, $types, $params];
    }

    public function getByPatient(int $patientId, int $page, array $filters = []): array {
        require_once __DIR__ . '/../config/config.php';
        $limit  = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $limit;

        [$where, $types, $params] = $this->buildWhere($filters);

        $baseWhere = 'a.patient_id = ?';
        $where     = $where ? $where . ' AND ' . $baseWhere : 'WHERE ' . $baseWhere;
        $types    .= 'iii';
        $params    = array_merge($params, [$patientId, $limit, $offset]);

        $result = $this->execute(
            'SELECT a.*, u_d.name AS doctor_name, s.name AS specialization_name
             FROM appointments a
             JOIN doctors d ON d.id = a.doctor_id
             JOIN users u_d ON u_d.id = d.user_id
             JOIN specializations s ON s.id = d.specialization_id
             JOIN users u_p ON u_p.id = a.patient_id
             ' . $where . '
             ORDER BY a.appt_date DESC, a.appt_time DESC LIMIT ? OFFSET ?',
            $types, $params
        );
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getByDoctor(int $doctorId, int $page, array $filters = []): array {
        require_once __DIR__ . '/../config/config.php';
        $limit  = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $limit;

        [$where, $types, $params] = $this->buildWhere($filters);
        $baseWhere = 'a.doctor_id = ?';
        $where     = $where ? $where . ' AND ' . $baseWhere : 'WHERE ' . $baseWhere;
        $types     = $types . 'iii';
        $params    = array_merge($params, [$doctorId, $limit, $offset]);

        $result = $this->execute(
            'SELECT a.*, u_p.name AS patient_name, u_p.email AS patient_email
             FROM appointments a
             JOIN users u_p ON u_p.id = a.patient_id
             JOIN doctors d ON d.id = a.doctor_id
             JOIN users u_d ON u_d.id = d.user_id
             JOIN specializations s ON s.id = d.specialization_id
             ' . $where . '
             ORDER BY a.appt_date DESC, a.appt_time DESC LIMIT ? OFFSET ?',
            $types, $params
        );
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getAll(int $page, array $filters = []): array {
        require_once __DIR__ . '/../config/config.php';
        $limit  = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $limit;

        [$where, $types, $params] = $this->buildWhere($filters);
        $types  .= 'ii';
        $params  = array_merge($params, [$limit, $offset]);

        $result = $this->execute(
            'SELECT a.*, u_p.name AS patient_name, u_d.name AS doctor_name, s.name AS specialization_name
             FROM appointments a
             JOIN users u_p ON u_p.id = a.patient_id
             JOIN doctors d ON d.id = a.doctor_id
             JOIN users u_d ON u_d.id = d.user_id
             JOIN specializations s ON s.id = d.specialization_id
             ' . $where . '
             ORDER BY a.appt_date DESC, a.appt_time DESC LIMIT ? OFFSET ?',
            $types, $params
        );
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function countFiltered(string $scope, int $scopeId, array $filters = []): int {
        [$where, $types, $params] = $this->buildWhere($filters);

        if ($scope === 'patient') {
            $extra = 'a.patient_id = ?';
            $types .= 'i';
            $params[] = $scopeId;
        } elseif ($scope === 'doctor') {
            $extra = 'a.doctor_id = ?';
            $types .= 'i';
            $params[] = $scopeId;
        } else {
            $extra = '';
        }

        if ($extra) {
            $where = $where ? $where . ' AND ' . $extra : 'WHERE ' . $extra;
        }

        $result = $this->execute(
            'SELECT COUNT(*) as total
             FROM appointments a
             JOIN users u_p ON u_p.id = a.patient_id
             JOIN doctors d ON d.id = a.doctor_id
             JOIN users u_d ON u_d.id = d.user_id
             JOIN specializations s ON s.id = d.specialization_id
             ' . $where,
            $types, $params
        );
        return (int) $result->fetch_assoc()['total'];
    }

    public function updateStatus(int $id, string $status, string $notes = ''): bool {
        $result = $this->execute(
            'UPDATE appointments SET status = ?, doctor_notes = ? WHERE id = ?',
            'ssi', [$status, $notes, $id]
        );
        return $result === true;
    }

    public function getTodayByDoctor(int $doctorId): array {
        $result = $this->execute(
            'SELECT a.*, u_p.name AS patient_name
             FROM appointments a
             JOIN users u_p ON u_p.id = a.patient_id
             WHERE a.doctor_id = ? AND a.appt_date = CURDATE()
             ORDER BY a.appt_time',
            'i', [$doctorId]
        );
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getUpcomingByDoctor(int $doctorId, int $limit = 5): array {
        $result = $this->execute(
            'SELECT a.*, u_p.name AS patient_name
             FROM appointments a
             JOIN users u_p ON u_p.id = a.patient_id
             WHERE a.doctor_id = ? AND a.appt_date >= CURDATE()
             AND a.status IN ("pending","confirmed")
             ORDER BY a.appt_date, a.appt_time LIMIT ?',
            'ii', [$doctorId, $limit]
        );
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getRecentAll(int $limit = 5): array {
        $result = $this->execute(
            'SELECT a.*, u_p.name AS patient_name, u_d.name AS doctor_name
             FROM appointments a
             JOIN users u_p ON u_p.id = a.patient_id
             JOIN doctors d ON d.id = a.doctor_id
             JOIN users u_d ON u_d.id = d.user_id
             ORDER BY a.created_at DESC LIMIT ?',
            'i', [$limit]
        );
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function countToday(): int {
        $result = $this->execute(
            'SELECT COUNT(*) as total FROM appointments WHERE appt_date = CURDATE()'
        );
        return (int) $result->fetch_assoc()['total'];
    }

    public function countThisWeekByStatus(): array {
        $result = $this->execute(
            'SELECT status, COUNT(*) as total FROM appointments
             WHERE WEEK(appt_date) = WEEK(NOW()) AND YEAR(appt_date) = YEAR(NOW())
             GROUP BY status'
        );
        $counts = [];
        while ($row = $result->fetch_assoc()) {
            $counts[$row['status']] = (int) $row['total'];
        }
        return $counts;
    }

    public function getNextByPatient(int $patientId): ?array {
        $result = $this->execute(
            'SELECT a.*, u_d.name AS doctor_name, s.name AS specialization_name
             FROM appointments a
             JOIN doctors d ON d.id = a.doctor_id
             JOIN users u_d ON u_d.id = d.user_id
             JOIN specializations s ON s.id = d.specialization_id
             WHERE a.patient_id = ? AND a.appt_date >= CURDATE()
             AND a.status IN ("pending","confirmed")
             ORDER BY a.appt_date, a.appt_time LIMIT 1',
            'i', [$patientId]
        );
        $row = $result->fetch_assoc();
        return $row ?: null;
    }

    public function getForReport(array $filters): array {
        [$where, $types, $params] = $this->buildWhere($filters);
        $result = $this->execute(
            'SELECT a.*, u_p.name AS patient_name, u_d.name AS doctor_name, s.name AS specialization_name
             FROM appointments a
             JOIN users u_p ON u_p.id = a.patient_id
             JOIN doctors d ON d.id = a.doctor_id
             JOIN users u_d ON u_d.id = d.user_id
             JOIN specializations s ON s.id = d.specialization_id
             ' . $where . '
             ORDER BY a.appt_date, a.appt_time',
            $types, $params
        );
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function countByDoctorThisMonth(int $doctorId): int {
        $result = $this->execute(
            'SELECT COUNT(*) as total FROM appointments
             WHERE doctor_id = ? AND MONTH(appt_date) = MONTH(NOW()) AND YEAR(appt_date) = YEAR(NOW())',
            'i', [$doctorId]
        );
        return (int) $result->fetch_assoc()['total'];
    }
}
