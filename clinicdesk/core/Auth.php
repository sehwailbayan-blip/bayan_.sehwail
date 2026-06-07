<?php
require_once __DIR__ . '/../config/config.php';

class Auth {
    public static function login(array $user): void {
        $_SESSION['user'] = [
            'id'   => $user['id'],
            'name' => $user['name'],
            'role' => $user['role'],
        ];
        session_regenerate_id(true);
    }

    public static function logout(): void {
        session_unset();
        session_destroy();
        header('Location: ' . BASE_URL . '/index.php?page=auth&action=login');
        exit;
    }

    public static function check(): bool {
        return isset($_SESSION['user']);
    }

    public static function currentUser(): ?array {
        return $_SESSION['user'] ?? null;
    }

    public static function role(): string {
        return $_SESSION['user']['role'] ?? '';
    }

    public static function requireRole(string ...$roles): void {
        if (!self::check()) {
            header('Location: ' . BASE_URL . '/index.php?page=auth&action=login');
            exit;
        }
        if (!in_array(self::role(), $roles)) {
            require_once __DIR__ . '/../views/errors/403.php';
            exit;
        }
    }
}
