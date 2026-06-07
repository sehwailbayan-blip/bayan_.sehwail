<?php
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/helpers.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/AppointmentModel.php';
require_once __DIR__ . '/../models/DoctorModel.php';
require_once __DIR__ . '/../models/PrescriptionModel.php';

class DashboardController {
    public function index(): void {
        Auth::requireRole('admin', 'doctor', 'patient');
        $role = Auth::role();
        $user = Auth::currentUser();

        if ($role === 'admin') {
            $this->adminDashboard($user);
        } elseif ($role === 'doctor') {
            $this->doctorDashboard($user);
        } else {
            $this->patientDashboard($user);
        }
    }

    private function adminDashboard(array $user): void {
        $userModel  = new UserModel();
        $apptModel  = new AppointmentModel();
        $roleCounts = $userModel->countByRole();
        $today      = $apptModel->countToday();
        $weekStats  = $apptModel->countThisWeekByStatus();
        $recent     = $apptModel->getRecentAll(5);

        $pageTitle = 'Admin Dashboard — ' . APP_NAME;
        require_once __DIR__ . '/../views/dashboard/admin.php';
    }

    private function doctorDashboard(array $user): void {
        $doctorModel = new DoctorModel();
        $apptModel   = new AppointmentModel();
        $doctor      = $doctorModel->findByUserId($user['id']);

        if (!$doctor) {
            flash('error', 'Doctor profile not found.');
            redirect(BASE_URL . '/index.php?page=auth&action=login');
        }

        $todayAppts    = $apptModel->getTodayByDoctor($doctor['id']);
        $upcoming      = $apptModel->getUpcomingByDoctor($doctor['id'], 5);
        $monthTotal    = $apptModel->countByDoctorThisMonth($doctor['id']);
        $pendingCount  = count(array_filter($todayAppts, fn($a) => $a['status'] === 'pending'));
        $completedCount= count(array_filter($todayAppts, fn($a) => $a['status'] === 'completed'));

        $pageTitle = 'Doctor Dashboard — ' . APP_NAME;
        require_once __DIR__ . '/../views/dashboard/doctor.php';
    }

    private function patientDashboard(array $user): void {
        $apptModel  = new AppointmentModel();
        $prescModel = new PrescriptionModel();

        $nextAppt        = $apptModel->getNextByPatient($user['id']);
        $activeCount     = $apptModel->countFiltered('patient', $user['id'], ['status' => 'pending'])
                         + $apptModel->countFiltered('patient', $user['id'], ['status' => 'confirmed']);
        $completedCount  = $apptModel->countFiltered('patient', $user['id'], ['status' => 'completed']);
        $prescCount      = $prescModel->countByPatient($user['id']);

        $pageTitle = 'My Dashboard — ' . APP_NAME;
        require_once __DIR__ . '/../views/dashboard/patient.php';
    }
}
