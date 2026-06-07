<?php
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/CSRF.php';
require_once __DIR__ . '/../core/helpers.php';
require_once __DIR__ . '/../models/SpecializationModel.php';

class SpecializationController {
    private SpecializationModel $specs;

    public function __construct() {
        $this->specs = new SpecializationModel();
    }

    public function index(): void {
        Auth::requireRole('admin');
        $list      = $this->specs->getAll();
        $pageTitle = 'Specializations — ' . APP_NAME;
        require_once __DIR__ . '/../views/specializations/index.php';
    }

    public function store(): void {
        Auth::requireRole('admin');
        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            flash('error', 'Invalid request.');
            redirect(BASE_URL . '/index.php?page=specializations');
        }
        $name = trim($_POST['name'] ?? '');
        if (!$name) {
            flash('error', 'Name is required.');
            redirect(BASE_URL . '/index.php?page=specializations');
        }
        $this->specs->create($name);
        flash('success', 'Specialization added.');
        redirect(BASE_URL . '/index.php?page=specializations');
    }

    public function delete(): void {
        Auth::requireRole('admin');
        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            flash('error', 'Invalid request.');
            redirect(BASE_URL . '/index.php?page=specializations');
        }
        $id = (int)($_POST['id'] ?? 0);
        if (!$this->specs->isSafeToDelete($id)) {
            flash('error', 'Cannot delete: doctors are using this specialization.');
            redirect(BASE_URL . '/index.php?page=specializations');
        }
        $this->specs->delete($id);
        flash('success', 'Specialization deleted.');
        redirect(BASE_URL . '/index.php?page=specializations');
    }
}
