<?php
require_once __DIR__ . '/BaseModel.php';

class DoctorModel extends BaseModel {
    public function findByUserId(int $userId): ?array {
        $result = $this->execute(
            'SELECT d.*, u.name, u.email, u.phone, u.avatar, s.name AS specialization_name
             FROM doctors d
             JOIN users u ON u.id = d.user_id
             JOIN specializations s ON s.id = d.specialization_id
             WHERE d.user_id = ?',
            'i', [$userId]
        );
        $row = $result->fetch_assoc();
        return $row ?: null;
    }

    public function findById(int $id): ?array {
        $result = $this->execute(
            'SELECT d.*, u.name, u.email, u.phone, u.avatar, s.name AS specialization_name
             FROM doctors d
             JOIN users u ON u.id = d.user_id
             JOIN specializations s ON s.id = d.specialization_id
             WHERE d.id = ?',
            'i', [$id]
        );
        $row = $result->fetch_assoc();
        return $row ?: null;
    }

    public function getAll(): array {
        $result = $this->execute(
            'SELECT d.*, u.name, u.email, s.name AS specialization_name
             FROM doctors d
             JOIN users u ON u.id = d.user_id
             JOIN specializations s ON s.id = d.specialization_id
             ORDER BY u.name'
        );
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getAllPaginated(int $page): array {
        require_once __DIR__ . '/../config/config.php';
        $limit  = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $limit;
        $result = $this->execute(
            'SELECT d.*, u.name, u.email, u.is_active, s.name AS specialization_name
             FROM doctors d
             JOIN users u ON u.id = d.user_id
             JOIN specializations s ON s.id = d.specialization_id
             ORDER BY u.name LIMIT ? OFFSET ?',
            'ii', [$limit, $offset]
        );
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function countAll(): int {
        $result = $this->execute('SELECT COUNT(*) as total FROM doctors');
        return (int) $result->fetch_assoc()['total'];
    }

    public function create(array $data): int {
        $this->execute(
            'INSERT INTO doctors (user_id, specialization_id, bio, consultation_fee, available_days)
             VALUES (?, ?, ?, ?, ?)',
            'iisds',
            [$data['user_id'], $data['specialization_id'], $data['bio'] ?? null,
             $data['consultation_fee'] ?? 0.00, $data['available_days'] ?? 'Sun,Mon,Tue,Wed,Thu']
        );
        return $this->db->lastInsertId();
    }

    public function update(int $doctorId, array $data): bool {
        $result = $this->execute(
            'UPDATE doctors SET specialization_id = ?, bio = ?, consultation_fee = ?, available_days = ? WHERE id = ?',
            'issdi',
            [$data['specialization_id'], $data['bio'] ?? null,
             $data['consultation_fee'] ?? 0.00, $data['available_days'], $doctorId]
        );
        return $result === true;
    }

    public function getAvailableDays(int $doctorId): array {
        $result = $this->execute('SELECT available_days FROM doctors WHERE id = ?', 'i', [$doctorId]);
        $row = $result->fetch_assoc();
        if (!$row) return [];
        return explode(',', $row['available_days']);
    }
}
