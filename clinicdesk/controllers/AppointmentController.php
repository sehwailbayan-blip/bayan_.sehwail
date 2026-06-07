<?php
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/CSRF.php';
require_once __DIR__ . '/../core/Paginator.php';
require_once __DIR__ . '/../core/helpers.php';
require_once __DIR__ . '/../models/AppointmentModel.php';
require_once __DIR__ . '/../models/DoctorModel.php';
require_once __DIR__ . '/../models/PrescriptionModel.php';
require_once __DIR__ . '/../config/config.php';

class AppointmentController {
    private AppointmentModel $appts;

    public function __construct() {
        $this->appts = new AppointmentModel();
    }

    public function book(): void {
        Auth::requireRole('patient');
        $doctors   = (new DoctorModel())->getAll();
        $pageTitle = 'Book Appointment — ' . APP_NAME;
        require_once __DIR__ . '/../views/appointments/book.php';
    }

    public function store(): void {
        Auth::requireRole('patient');
        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            flash('error', 'Invalid request.');
            redirect(BASE_URL . '/index.php?page=appointments&action=book');
        }

        $doctorId = (int)($_POST['doctor_id'] ?? 0);
        $date     = $_POST['appt_date'] ?? '';
        $time     = $_POST['appt_time'] ?? '';
        $reason   = trim($_POST['reason'] ?? '');

        if (!$doctorId || !$date || !$time) {
            flash('error', 'All fields are required.');
            redirect(BASE_URL . '/index.php?page=appointments&action=book');
        }

        if (strtotime($date) < strtotime(date('Y-m-d'))) {
            flash('error', 'Appointment date cannot be in the past.');
            redirect(BASE_URL . '/index.php?page=appointments&action=book');
        }

        $doctorModel = new DoctorModel();
        $doctor      = $doctorModel->findById($doctorId);
        if (!$doctor) {
            flash('error', 'Doctor not found.');
            redirect(BASE_URL . '/index.php?page=appointments&action=book');
        }

        $availDays = explode(',', $doctor['available_days']);
        $dayName   = date('D', strtotime($date));
        if (!in_array($dayName, $availDays)) {
            flash('error', 'Doctor is not available on that day. Available: ' . e($doctor['available_days']));
            redirect(BASE_URL . '/index.php?page=appointments&action=book');
        }

        if ($this->appts->hasConflict($doctorId, $date, $time)) {
            flash('error', 'This slot is already booked, please choose another time.');
            redirect(BASE_URL . '/index.php?page=appointments&action=book');
        }

        $ok = $this->appts->book([
            'patient_id' => Auth::currentUser()['id'],
            'doctor_id'  => $doctorId,
            'appt_date'  => $date,
            'appt_time'  => $time,
            'reason'     => $reason,
        ]);

        if (!$ok) {
            flash('error', 'This slot is already booked, please choose another time.');
            redirect(BASE_URL . '/index.php?page=appointments&action=book');
        }

        flash('success', 'Appointment booked successfully.');
        redirect(BASE_URL . '/index.php?page=appointments&action=mine');
    }

    public function mine(): void {
        Auth::requireRole('patient');
        $user    = Auth::currentUser();
        $page    = max(1, (int)($_GET['p'] ?? 1));
        $filters = $this->collectFilters();
        $total   = $this->appts->countFiltered('patient', $user['id'], $filters);
        $pager   = new Paginator($total, ITEMS_PER_PAGE, $page);
        $list    = $this->appts->getByPatient($user['id'], $page, $filters);

        $pageTitle = 'My Appointments — ' . APP_NAME;
        require_once __DIR__ . '/../views/appointments/mine.php';
    }

    public function schedule(): void {
        Auth::requireRole('doctor');
        $doctorModel = new DoctorModel();
        $doctor      = $doctorModel->findByUserId(Auth::currentUser()['id']);
        if (!$doctor) {
            flash('error', 'Doctor profile not found.');
            redirect(BASE_URL . '/index.php?page=dashboard');
        }

        $page    = max(1, (int)($_GET['p'] ?? 1));
        $filters = $this->collectFilters();
        $total   = $this->appts->countFiltered('doctor', $doctor['id'], $filters);
        $pager   = new Paginator($total, ITEMS_PER_PAGE, $page);
        $list    = $this->appts->getByDoctor($doctor['id'], $page, $filters);
        $today   = $this->appts->getTodayByDoctor($doctor['id']);

        $pageTitle = 'My Schedule — ' . APP_NAME;
        require_once __DIR__ . '/../views/appointments/schedule.php';
    }

    public function all(): void {
        Auth::requireRole('admin');
        $page    = max(1, (int)($_GET['p'] ?? 1));
        $filters = $this->collectFilters();
        $total   = $this->appts->countFiltered('all', 0, $filters);
        $pager   = new Paginator($total, ITEMS_PER_PAGE, $page);
        $list    = $this->appts->getAll($page, $filters);
        $doctors = (new DoctorModel())->getAll();

        $pageTitle = 'All Appointments — ' . APP_NAME;
        require_once __DIR__ . '/../views/appointments/all.php';
    }

    public function detail(): void {
        Auth::requireRole('admin', 'doctor', 'patient');
        $id   = (int)($_GET['id'] ?? 0);
        $appt = $this->appts->findById($id);
        if (!$appt) {
            flash('error', 'Appointment not found.');
            redirect(BASE_URL . '/index.php?page=appointments');
        }

        $user = Auth::currentUser();
        $role = Auth::role();

        if ($role === 'patient' && (int)$appt['patient_id'] !== (int)$user['id']) {
            require_once __DIR__ . '/../views/errors/403.php';
            exit;
        }
        if ($role === 'doctor') {
            $doctor = (new DoctorModel())->findByUserId($user['id']);
            if (!$doctor || (int)$appt['doctor_id'] !== (int)$doctor['id']) {
                require_once __DIR__ . '/../views/errors/403.php';
                exit;
            }
        }

        $prescription = (new PrescriptionModel())->findByAppointmentId($id);
        $pageTitle    = 'Appointment Detail — ' . APP_NAME;
        require_once __DIR__ . '/../views/appointments/detail.php';
    }

    public function updateStatus(): void {
        Auth::requireRole('admin', 'doctor');
        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            flash('error', 'Invalid request.');
            redirect(BASE_URL . '/index.php?page=appointments');
        }

        $id     = (int)($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? '';
        $notes  = trim($_POST['doctor_notes'] ?? '');

        $appt = $this->appts->findById($id);
        if (!$appt) {
            flash('error', 'Appointment not found.');
            redirect(BASE_URL . '/index.php?page=appointments');
        }

        if (Auth::role() === 'doctor') {
            $doctor = (new DoctorModel())->findByUserId(Auth::currentUser()['id']);
            if (!$doctor || (int)$appt['doctor_id'] !== (int)$doctor['id']) {
                require_once __DIR__ . '/../views/errors/403.php';
                exit;
            }
        }

        $this->appts->updateStatus($id, $status, $notes);
        flash('success', 'Appointment status updated.');
        redirect(BASE_URL . '/index.php?page=appointments&action=detail&id=' . $id);
    }

    public function cancel(): void {
        Auth::requireRole('patient');
        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            flash('error', 'Invalid request.');
            redirect(BASE_URL . '/index.php?page=appointments&action=mine');
        }

        $id   = (int)($_POST['id'] ?? 0);
        $appt = $this->appts->findById($id);
        if (!$appt || (int)$appt['patient_id'] !== (int)Auth::currentUser()['id']) {
            flash('error', 'Appointment not found.');
            redirect(BASE_URL . '/index.php?page=appointments&action=mine');
        }

        if ($appt['status'] !== 'pending') {
            flash('error', 'Only pending appointments can be cancelled.');
            redirect(BASE_URL . '/index.php?page=appointments&action=mine');
        }

        $this->appts->updateStatus($id, 'cancelled');
        flash('success', 'Appointment cancelled.');
        redirect(BASE_URL . '/index.php?page=appointments&action=mine');
    }

    private function collectFilters(): array {
        $filters = [];
        if (!empty($_GET['status']))     $filters['status']      = $_GET['status'];
        if (!empty($_GET['doctor_id']))   $filters['doctor_id']   = (int)$_GET['doctor_id'];
        if (!empty($_GET['start_date']))  $filters['start_date']  = $_GET['start_date'];
        if (!empty($_GET['end_date']))    $filters['end_date']    = $_GET['end_date'];
        if (!empty($_GET['patient_name']))$filters['patient_name']= $_GET['patient_name'];
        return $filters;
    }
}
