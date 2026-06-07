<?php
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/CSRF.php';
require_once __DIR__ . '/../core/helpers.php';
require_once __DIR__ . '/../models/PrescriptionModel.php';
require_once __DIR__ . '/../models/AppointmentModel.php';
require_once __DIR__ . '/../models/DoctorModel.php';
require_once __DIR__ . '/../config/config.php';

class PrescriptionController {
    private PrescriptionModel $prescriptions;

    public function __construct() {
        $this->prescriptions = new PrescriptionModel();
    }

    public function add(): void {
        Auth::requireRole('doctor');
        $apptId = (int)($_GET['appt_id'] ?? 0);
        $appt   = (new AppointmentModel())->findById($apptId);

        if (!$appt) {
            flash('error', 'Appointment not found.');
            redirect(BASE_URL . '/index.php?page=appointments&action=schedule');
        }

        $doctor = (new DoctorModel())->findByUserId(Auth::currentUser()['id']);
        if (!$doctor || (int)$appt['doctor_id'] !== (int)$doctor['id']) {
            require_once __DIR__ . '/../views/errors/403.php';
            exit;
        }

        if ($appt['status'] !== 'completed') {
            flash('error', 'Prescriptions can only be added to completed appointments.');
            redirect(BASE_URL . '/index.php?page=appointments&action=detail&id=' . $apptId);
        }

        if ($this->prescriptions->findByAppointmentId($apptId)) {
            flash('error', 'A prescription already exists for this appointment.');
            redirect(BASE_URL . '/index.php?page=appointments&action=detail&id=' . $apptId);
        }

        $pageTitle = 'Add Prescription — ' . APP_NAME;
        require_once __DIR__ . '/../views/prescriptions/add.php';
    }

    public function store(): void {
        Auth::requireRole('doctor');
        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            flash('error', 'Invalid request.');
            redirect(BASE_URL . '/index.php?page=appointments&action=schedule');
        }

        $apptId     = (int)($_POST['appt_id'] ?? 0);
        $diagnosis  = trim($_POST['diagnosis'] ?? '');
        $medications= trim($_POST['medications'] ?? '');
        $notes      = trim($_POST['notes'] ?? '');

        if (!$diagnosis || !$medications) {
            flash('error', 'Diagnosis and medications are required.');
            redirect(BASE_URL . '/index.php?page=prescriptions&action=add&appt_id=' . $apptId);
        }

        $appt   = (new AppointmentModel())->findById($apptId);
        $doctor = (new DoctorModel())->findByUserId(Auth::currentUser()['id']);

        if (!$appt || !$doctor || (int)$appt['doctor_id'] !== (int)$doctor['id']) {
            require_once __DIR__ . '/../views/errors/403.php';
            exit;
        }

        $filePath = null;
        if (!empty($_FILES['prescription_file']['name'])) {
            $upload = $this->handlePdfUpload($_FILES['prescription_file'], $apptId);
            if ($upload === false) {
                flash('error', 'Invalid file. Only PDF allowed, max 3MB.');
                redirect(BASE_URL . '/index.php?page=prescriptions&action=add&appt_id=' . $apptId);
            }
            $filePath = $upload;
        }

        $this->prescriptions->create([
            'appointment_id' => $apptId,
            'diagnosis'      => $diagnosis,
            'medications'    => $medications,
            'notes'          => $notes,
            'file_path'      => $filePath,
        ]);

        flash('success', 'Prescription added successfully.');
        redirect(BASE_URL . '/index.php?page=appointments&action=detail&id=' . $apptId);
    }

    public function myPrescriptions(): void {
        Auth::requireRole('patient');
        $list      = $this->prescriptions->getByPatient(Auth::currentUser()['id']);
        $pageTitle = 'My Prescriptions — ' . APP_NAME;
        require_once __DIR__ . '/../views/prescriptions/mine.php';
    }

    public function download(): void {
        Auth::requireRole('admin', 'doctor', 'patient');
        $id   = (int)($_GET['id'] ?? 0);
        $presc= $this->prescriptions->findById($id);

        if (!$presc) {
            flash('error', 'Prescription not found.');
            redirect(BASE_URL . '/index.php?page=dashboard');
        }

        $user = Auth::currentUser();
        $role = Auth::role();

        if ($role === 'patient' && (int)$presc['patient_id'] !== (int)$user['id']) {
            require_once __DIR__ . '/../views/errors/403.php';
            exit;
        }
        if ($role === 'doctor') {
            $doctor = (new DoctorModel())->findByUserId($user['id']);
            if (!$doctor || (int)$presc['doctor_id'] !== (int)$doctor['id']) {
                require_once __DIR__ . '/../views/errors/403.php';
                exit;
            }
        }

        if (!$presc['file_path']) {
            flash('error', 'No file attached to this prescription.');
            redirect(BASE_URL . '/index.php?page=prescriptions&action=mine');
        }

        $fullPath = UPLOAD_PRESCRIPTIONS . $presc['file_path'];
        if (!file_exists($fullPath)) {
            flash('error', 'File not found on server.');
            redirect(BASE_URL . '/index.php?page=prescriptions&action=mine');
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="prescription.pdf"');
        header('Content-Length: ' . filesize($fullPath));
        readfile($fullPath);
        exit;
    }

    private function handlePdfUpload(array $file, int $apptId): string|false {
        if ($file['error'] !== UPLOAD_ERR_OK) return false;
        if ($file['size'] > MAX_PRESCRIPTION_SIZE) return false;
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($file['tmp_name']);
        if ($mime !== 'application/pdf') return false;
        $filename = 'prescription_' . $apptId . '_' . time() . '.pdf';
        move_uploaded_file($file['tmp_name'], UPLOAD_PRESCRIPTIONS . $filename);
        return $filename;
    }
}
