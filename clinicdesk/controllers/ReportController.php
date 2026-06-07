<?php
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/helpers.php';
require_once __DIR__ . '/../models/AppointmentModel.php';
require_once __DIR__ . '/../models/DoctorModel.php';

class ReportController {
    public function index(): void {
        Auth::requireRole('admin');
        $apptModel = new AppointmentModel();
        $doctors   = (new DoctorModel())->getAll();
        $results   = [];
        $filters   = [];

        $start  = $_GET['start_date'] ?? '';
        $end    = $_GET['end_date']   ?? '';

        if ($start && $end) {
            if ($start > $end) {
                flash('error', 'Start date must be before or equal to end date.');
                redirect(BASE_URL . '/index.php?page=reports');
            }
            $filters['start_date'] = $start;
            $filters['end_date']   = $end;
            if (!empty($_GET['doctor_id'])) $filters['doctor_id'] = (int)$_GET['doctor_id'];
            if (!empty($_GET['status']))    $filters['status']    = $_GET['status'];

            $results = $apptModel->getForReport($filters);

            if (($_GET['export'] ?? '') === 'csv') {
                $this->exportCsv($results);
            }
        }

        $pageTitle = 'Reports — ' . APP_NAME;
        require_once __DIR__ . '/../views/reports/index.php';
    }

    private function exportCsv(array $results): never {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="report_' . date('Ymd_His') . '.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Patient', 'Doctor', 'Specialization', 'Date', 'Time', 'Status', 'Reason']);
        foreach ($results as $row) {
            fputcsv($out, [
                $row['patient_name'],
                $row['doctor_name'],
                $row['specialization_name'],
                $row['appt_date'],
                $row['appt_time'],
                $row['status'],
                $row['reason'],
            ]);
        }
        fclose($out);
        exit;
    }
}
