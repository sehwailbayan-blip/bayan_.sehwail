<?php
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/CSRF.php';
require_once __DIR__ . '/../core/Paginator.php';
require_once __DIR__ . '/../core/helpers.php';
require_once __DIR__ . '/../models/DoctorModel.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/SpecializationModel.php';
require_once __DIR__ . '/../config/config.php';

class DoctorController {
    private DoctorModel $doctors;

    public function __construct() {
        $this->doctors = new DoctorModel();
    }

    public function index(): void {
        Auth::requireRole('admin');
        $page      = max(1, (int)($_GET['p'] ?? 1));
        $total     = $this->doctors->countAll();
        $paginator = new Paginator($total, ITEMS_PER_PAGE, $page);
        $list      = $this->doctors->getAllPaginated($page);
        $pageTitle = 'Manage Doctors — ' . APP_NAME;
        require_once __DIR__ . '/../views/doctors/index.php';
    }

    public function edit(): void {
        Auth::requireRole('admin', 'doctor');
        $id     = (int)($_GET['id'] ?? 0);
        $doctor = $this->doctors->findById($id);
        if (!$doctor) {
            flash('error', 'Doctor not found.');
            redirect(BASE_URL . '/index.php?page=doctors');
        }
        if (Auth::role() === 'doctor') {
            $mine = $this->doctors->findByUserId(Auth::currentUser()['id']);
            if (!$mine || $mine['id'] !== $doctor['id']) {
                require_once __DIR__ . '/../views/errors/403.php';
                exit;
            }
        }
        $specs     = (new SpecializationModel())->getAll();
        $pageTitle = 'Edit Doctor — ' . APP_NAME;
        require_once __DIR__ . '/../views/doctors/edit.php';
    }

    public function update(): void {
        Auth::requireRole('admin', 'doctor');
        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            flash('error', 'Invalid request.');
            redirect(BASE_URL . '/index.php?page=doctors');
        }

        $doctorId = (int)($_POST['doctor_id'] ?? 0);
        $doctor   = $this->doctors->findById($doctorId);
        if (!$doctor) {
            flash('error', 'Doctor not found.');
            redirect(BASE_URL . '/index.php?page=doctors');
        }

        if (Auth::role() === 'doctor') {
            $mine = $this->doctors->findByUserId(Auth::currentUser()['id']);
            if (!$mine || $mine['id'] !== $doctor['id']) {
                require_once __DIR__ . '/../views/errors/403.php';
                exit;
            }
        }

        $days = isset($_POST['available_days'])
            ? implode(',', array_map('trim', (array)$_POST['available_days']))
            : '';

        $data = [
            'specialization_id' => (int)($_POST['specialization_id'] ?? $doctor['specialization_id']),
            'bio'               => trim($_POST['bio'] ?? ''),
            'consultation_fee'  => (float)($_POST['consultation_fee'] ?? 0),
            'available_days'    => $days,
        ];

        if (!empty($_FILES['photo']['name'])) {
            $upload = $this->handlePhotoUpload($_FILES['photo'], $doctor['user_id']);
            if ($upload === false) {
                flash('error', 'Invalid photo. Use JPEG or PNG under 1MB.');
                redirect(BASE_URL . '/index.php?page=doctors&action=edit&id=' . $doctorId);
            }
            $userModel = new UserModel();
            $user = $userModel->findById($doctor['user_id']);
            if ($user['avatar'] && file_exists(UPLOAD_DOCTOR_PHOTOS . $user['avatar'])) {
                unlink(UPLOAD_DOCTOR_PHOTOS . $user['avatar']);
            }
            $userModel->update($doctor['user_id'], [
                'name'   => $user['name'],
                'phone'  => $user['phone'],
                'avatar' => $upload,
            ]);
        }

        $this->doctors->update($doctorId, $data);
        flash('success', 'Doctor profile updated.');

        if (Auth::role() === 'doctor') {
            redirect(BASE_URL . '/index.php?page=dashboard');
        }
        redirect(BASE_URL . '/index.php?page=doctors');
    }

    private function handlePhotoUpload(array $file, int $userId): string|false {
        if ($file['error'] !== UPLOAD_ERR_OK) return false;
        if ($file['size'] > MAX_AVATAR_SIZE) return false;
        $info = @getimagesize($file['tmp_name']);
        if (!$info) return false;
        if (!in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG])) return false;
        $ext      = $info[2] === IMAGETYPE_JPEG ? 'jpg' : 'png';
        $filename = 'doc_' . $userId . '_' . time() . '.' . $ext;
        move_uploaded_file($file['tmp_name'], UPLOAD_DOCTOR_PHOTOS . $filename);
        return $filename;
    }
}
