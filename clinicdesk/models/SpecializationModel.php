<?php
require_once __DIR__ . '/BaseModel.php';

class SpecializationModel extends BaseModel {
    public function getAll(): array {
        $result = $this->execute('SELECT * FROM specializations ORDER BY name');
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function findById(int $id): ?array {
        $result = $this->execute('SELECT * FROM specializations WHERE id = ?', 'i', [$id]);
        $row = $result->fetch_assoc();
        return $row ?: null;
    }

    public function create(string $name): int {
        $this->execute('INSERT INTO specializations (name) VALUES (?)', 's', [$name]);
        return $this->db->lastInsertId();
    }

    public function delete(int $id): bool {
        $result = $this->execute('DELETE FROM specializations WHERE id = ?', 'i', [$id]);
        return $result === true;
    }

    public function isSafeToDelete(int $id): bool {
        $result = $this->execute(
            'SELECT COUNT(*) as total FROM doctors WHERE specialization_id = ?',
            'i', [$id]
        );
        return (int) $result->fetch_assoc()['total'] === 0;
    }
}
