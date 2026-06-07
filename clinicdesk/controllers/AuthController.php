<?php
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/CSRF.php';
require_once __DIR__ . '/../core/helpers.php';
require_once __DIR__ . '/../models/UserModel.php';

class AuthController {
    private UserModel $users;

    public function __construct() {
        $this->users = new UserModel();
    }

    public function login(): void {
        if (Auth::check()) {
            redirect(BASE_URL . '/index.php?page=dashboard');
        }
        $pageTitle = 'Login — ' . APP_NAME;
        require_once __DIR__ . '/../views/auth/login.php';
    }

    public function handleLogin(): void {
        $token = $_POST['csrf_token'] ?? '';
        if (!CSRF::validateToken($token)) {
            flash('error', 'Invalid request. Please try again.');
            redirect(BASE_URL . '/index.php?page=auth&action=login');
        }

        $email    = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';

        $user = $this->users->findByEmail($email);
        if (!$user || !password_verify($password, $user['password'])) {
            flash('error', 'Invalid credentials.');
            redirect(BASE_URL . '/index.php?page=auth&action=login');
        }

        if ((int) $user['is_active'] !== 1) {
            flash('error', 'Account suspended. Contact admin.');
            redirect(BASE_URL . '/index.php?page=auth&action=login');
        }

        Auth::login($user);
        redirect(BASE_URL . '/index.php?page=dashboard');
    }

    public function logout(): void {
        $token = $_POST['csrf_token'] ?? '';
        if (CSRF::validateToken($token)) {
            Auth::logout();
        }
        redirect(BASE_URL . '/index.php?page=auth&action=login');
    }
}
