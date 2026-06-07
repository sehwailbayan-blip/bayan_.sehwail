<?php
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/CSRF.php';
require_once __DIR__ . '/../core/Paginator.php';
require_once __DIR__ . '/../core/helpers.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/DoctorModel.php';
require_once __DIR__ . '/../models/SpecializationModel.php';
require_once __DIR__ . '/../config/config.php';

class UserController {
    private UserModel $users;

    public function __construct() {
        $this->users = new UserModel();
    }

    public function index(): void {
        Auth::requireRole('admin');
        $page       = max(1, (int)($_GET['p'] ?? 1));
        $role       = $_GET['role'] ?? '';
        $total      = $this->users->countAll($role);
        $paginator  = new Paginator($total, ITEMS_PER_PAGE, $page);
        $usersList  = $this->users->getAllPaginated($page, $role);
        $pageTitle  = 'Manage Users — ' . APP_NAME;
        require_once __DIR__ . '/../views/users/index.php';
    }

    public function create(): void {
        Auth::requireRole('admin');
        $specs     = (new SpecializationModel())->getAll();
        $pageTitle = 'Add User — ' . APP_NAME;
        require_once __DIR__ . '/../views/users/create.php';
    }

    public function store(): void {
        Auth::requireRole('admin');
        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            flash('error', 'Invalid request.');
            redirect(BASE_URL . '/index.php?page=users');
        }

        $name     = trim($_POST['name'] ?? '');
        $email    = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';
        $role     = $_POST['role'] ?? 'patient';
        $phone    = trim($_POST['phone'] ?? '');

        if (!$name || !$email || !$password) {
            flash('error', 'Name, email and password are required.');
            redirect(BASE_URL . '/index.php?page=users&action=create');
        }

        if ($this->users->findByEmail($email)) {
            flash('error', 'Email already in use.');
            redirect(BASE_URL . '/index.php?page=users&action=create');
        }

        $hash   = password_hash($password, PASSWORD_BCRYPT);
        $userId = $this->users->create(['name' => $name, 'email' => $email, 'password' => $hash, 'role' => $role, 'phone' => $phone]);

        if ($role === 'doctor') {
            $doctorModel = new DoctorModel();
            $days = isset($_POST['available_days']) ? implode(',', array_map('trim', $_POST['available_days'])) : 'Sun,Mon,Tue,Wed,Thu';
            $doctorModel->create([
                'user_id'          => $userId,
                'specialization_id'=> (int)($_POST['specialization_id'] ?? 1),
                'bio'              => trim($_POST['bio'] ?? ''),
                'consultation_fee' => (float)($_POST['consultation_fee'] ?? 0),
                'available_days'   => $days,
            ]);
        }

        flash('success', 'User created successfully.');
        redirect(BASE_URL . '/index.php?page=users');
    }

    public function edit(): void {
        Auth::requireRole('admin');
        $id   = (int)($_GET['id'] ?? 0);
        $user = $this->users->findById($id);
        if (!$user) {
            flash('error', 'User not found.');
            redirect(BASE_URL . '/index.php?page=users');
        }
        $pageTitle = 'Edit User — ' . APP_NAME;
        require_once __DIR__ . '/../views/users/edit.php';
    }

    public function update(): void {
        Auth::requireRole('admin');
        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            flash('error', 'Invalid request.');
            redirect(BASE_URL . '/index.php?page=users');
        }

        $id    = (int)($_POST['id'] ?? 0);
        $name  = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');

        $existing = $this->users->findById($id);
        if (!$existing) {
            flash('error', 'User not found.');
            redirect(BASE_URL . '/index.php?page=users');
        }

        $avatar = $existing['avatar'];
        if (!empty($_FILES['avatar']['name'])) {
            $upload = $this->handleAvatarUpload($_FILES['avatar']);
            if ($upload === false) {
                flash('error', 'Invalid image file.');
                redirect(BASE_URL . '/index.php?page=users&action=edit&id=' . $id);
            }
            if ($avatar && file_exists(UPLOAD_AVATARS . $avatar)) {
                unlink(UPLOAD_AVATARS . $avatar);
            }
            $avatar = $upload;
        }

        $this->users->update($id, ['name' => $name, 'phone' => $phone, 'avatar' => $avatar]);
        flash('success', 'User updated.');
        redirect(BASE_URL . '/index.php?page=users');
    }

    public function toggleActive(): void {
        Auth::requireRole('admin');
        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            flash('error', 'Invalid request.');
            redirect(BASE_URL . '/index.php?page=users');
        }

        $id      = (int)($_POST['id'] ?? 0);
        $current = Auth::currentUser();
        if ($id === (int)$current['id']) {
            flash('error', 'You cannot deactivate your own account.');
            redirect(BASE_URL . '/index.php?page=users');
        }

        $this->users->toggleActive($id);
        flash('success', 'Account status updated.');
        redirect(BASE_URL . '/index.php?page=users');
    }

    public function profile(): void {
        Auth::requireRole('admin', 'doctor', 'patient');
        $user      = $this->users->findById(Auth::currentUser()['id']);
        $pageTitle = 'My Profile — ' . APP_NAME;
        require_once __DIR__ . '/../views/users/profile.php';
    }

    public function updateProfile(): void {
        Auth::requireRole('admin', 'doctor', 'patient');
        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            flash('error', 'Invalid request.');
            redirect(BASE_URL . '/index.php?page=users&action=profile');
        }

        $id       = Auth::currentUser()['id'];
        $name     = trim($_POST['name'] ?? '');
        $phone    = trim($_POST['phone'] ?? '');
        $existing = $this->users->findById($id);
        $avatar   = $existing['avatar'];

        if (!empty($_FILES['avatar']['name'])) {
            $upload = $this->handleAvatarUpload($_FILES['avatar']);
            if ($upload === false) {
                flash('error', 'Invalid image file. Use JPEG or PNG under 1MB.');
                redirect(BASE_URL . '/index.php?page=users&action=profile');
            }
            if ($avatar && file_exists(UPLOAD_AVATARS . $avatar)) {
                unlink(UPLOAD_AVATARS . $avatar);
            }
            $avatar = $upload;
        }

        $this->users->update($id, ['name' => $name, 'phone' => $phone, 'avatar' => $avatar]);
        flash('success', 'Profile updated.');
        redirect(BASE_URL . '/index.php?page=users&action=profile');
    }

    private function handleAvatarUpload(array $file): string|false {
        if ($file['error'] !== UPLOAD_ERR_OK) return false;
        if ($file['size'] > MAX_AVATAR_SIZE) return false;
        $info = @getimagesize($file['tmp_name']);
        if (!$info) return false;
        $allowed = [IMAGETYPE_JPEG, IMAGETYPE_PNG];
        if (!in_array($info[2], $allowed)) return false;
        $ext      = $info[2] === IMAGETYPE_JPEG ? 'jpg' : 'png';
        $filename = uniqid('av_', true) . '.' . $ext;
        move_uploaded_file($file['tmp_name'], UPLOAD_AVATARS . $filename);
        return $filename;
    }
}
