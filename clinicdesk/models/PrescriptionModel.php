<?php
require_once __DIR__ . '/BaseModel.php';

class PrescriptionModel extends BaseModel {
    public function findByAppointmentId(int $apptId): ?array {
        $result = $this->execute(
            'SELECT * FROM prescriptions WHERE appointment_id = ?',
            'i', [$apptId]
        );
        $row = $result->fetch_assoc();
        return $row ?: null;
    }

    public function create(array $data): int {
        $this->execute(
            'INSERT INTO prescriptions (appointment_id, diagnosis, medications, notes, file_path)
             VALUES (?, ?, ?, ?, ?)',
            'issss',
            [$data['appointment_id'], $data['diagnosis'], $data['medications'],
             $data['notes'] ?? null, $data['file_path'] ?? null]
        );
        return $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        $result = $this->execute(
            'UPDATE prescriptions SET diagnosis = ?, medications = ?, notes = ?, file_path = ? WHERE id = ?',
            'ssssi',
            [$data['diagnosis'], $data['medications'], $data['notes'] ?? null,
             $data['file_path'] ?? null, $id]
        );
        return $result === true;
    }

    public function getByPatient(int $patientId): array {
        $result = $this->execute(
            'SELECT p.*, a.appt_date, a.appt_time, u_d.name AS doctor_name, s.name AS specialization_name
             FROM prescriptions p
             JOIN appointments a ON a.id = p.appointment_id
             JOIN doctors d ON d.id = a.doctor_id
             JOIN users u_d ON u_d.id = d.user_id
             JOIN specializations s ON s.id = d.specialization_id
             WHERE a.patient_id = ?
             ORDER BY a.appt_date DESC',
            'i', [$patientId]
        );
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function countByPatient(int $patientId): int {
        $result = $this->execute(
            'SELECT COUNT(*) as total
             FROM prescriptions p
             JOIN appointments a ON a.id = p.appointment_id
             WHERE a.patient_id = ?',
            'i', [$patientId]
        );
        return (int) $result->fetch_assoc()['total'];
    }

    public function findById(int $id): ?array {
        $result = $this->execute(
            'SELECT p.*, a.patient_id, a.doctor_id, a.appt_date
             FROM prescriptions p
             JOIN appointments a ON a.id = p.appointment_id
             WHERE p.id = ?',
            'i', [$id]
        );
        $row = $result->fetch_assoc();
        return $row ?: null;
    }
}
