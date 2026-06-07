<?php
require_once __DIR__ . '/../../core/Auth.php';
Auth::requireRole('doctor');
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';
require_once __DIR__ . '/../partials/sidebar.php';
?>

<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6"><h1 class="m-0">Add Prescription</h1></div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/index.php?page=appointments&action=schedule">Schedule</a></li>
            <li class="breadcrumb-item active">Add Prescription</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <section class="content">
    <div class="container-fluid">
      <?php require_once __DIR__ . '/../partials/alerts.php'; ?>

      <div class="card">
        <div class="card-header">
          <h3 class="card-title">
            Prescription for Appointment #<?= e($appt['id']) ?>
            — <?= e($appt['patient_name']) ?> (<?= e(formatDate($appt['appt_date'])) ?>)
          </h3>
        </div>
        <form method="POST" action="<?= BASE_URL ?>/index.php?page=prescriptions&action=store" enctype="multipart/form-data">
          <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken() ?>">
          <input type="hidden" name="appt_id" value="<?= e($appt['id']) ?>">
          <div class="card-body">
            <div class="form-group">
              <label>Diagnosis <span class="text-danger">*</span></label>
              <textarea name="diagnosis" class="form-control" rows="3" required></textarea>
            </div>
            <div class="form-group">
              <label>Medications <span class="text-danger">*</span></label>
              <textarea name="medications" class="form-control" rows="4" required></textarea>
            </div>
            <div class="form-group">
              <label>Notes (optional)</label>
              <textarea name="notes" class="form-control" rows="2"></textarea>
            </div>
            <div class="form-group">
              <label>Prescription PDF (optional, max 3MB)</label>
              <input type="file" name="prescription_file" class="form-control-file" accept=".pdf">
            </div>
          </div>
          <div class="card-footer">
            <button type="submit" class="btn btn-primary">Save Prescription</button>
            <a href="<?= BASE_URL ?>/index.php?page=appointments&action=detail&id=<?= e($appt['id']) ?>" class="btn btn-secondary">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </section>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
