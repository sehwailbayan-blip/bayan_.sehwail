<?php
require_once __DIR__ . '/../../core/Auth.php';
Auth::requireRole('admin', 'doctor', 'patient');
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';
require_once __DIR__ . '/../partials/sidebar.php';

$role  = Auth::role();
$badge = match($appt['status']) {
    'pending'   => 'warning',
    'confirmed' => 'info',
    'completed' => 'success',
    'cancelled' => 'danger',
    default     => 'secondary',
};
?>

<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6"><h1 class="m-0">Appointment Detail</h1></div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/index.php?page=appointments<?= $role === 'patient' ? '&action=mine' : ($role === 'doctor' ? '&action=schedule' : '') ?>">Appointments</a></li>
            <li class="breadcrumb-item active">Detail</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <section class="content">
    <div class="container-fluid">
      <?php require_once __DIR__ . '/../partials/alerts.php'; ?>

      <div class="row">
        <div class="col-md-6">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Appointment Info</h3>
              <span class="badge badge-<?= $badge ?> float-right"><?= e($appt['status']) ?></span>
            </div>
            <div class="card-body">
              <dl class="row">
                <dt class="col-sm-4">Patient</dt>
                <dd class="col-sm-8"><?= e($appt['patient_name']) ?></dd>
                <dt class="col-sm-4">Doctor</dt>
                <dd class="col-sm-8"><?= e($appt['doctor_name']) ?></dd>
                <dt class="col-sm-4">Specialization</dt>
                <dd class="col-sm-8"><?= e($appt['specialization_name']) ?></dd>
                <dt class="col-sm-4">Date</dt>
                <dd class="col-sm-8"><?= e(formatDate($appt['appt_date'])) ?></dd>
                <dt class="col-sm-4">Time</dt>
                <dd class="col-sm-8"><?= e(formatTime($appt['appt_time'])) ?></dd>
                <dt class="col-sm-4">Reason</dt>
                <dd class="col-sm-8"><?= e($appt['reason'] ?? '—') ?></dd>
                <?php if ($appt['doctor_notes']): ?>
                <dt class="col-sm-4">Doctor Notes</dt>
                <dd class="col-sm-8"><?= e($appt['doctor_notes']) ?></dd>
                <?php endif; ?>
              </dl>
            </div>
          </div>
        </div>

        <div class="col-md-6">
          <?php if (in_array($role, ['admin', 'doctor']) && !in_array($appt['status'], ['cancelled', 'completed'])): ?>
          <div class="card">
            <div class="card-header"><h3 class="card-title">Update Status</h3></div>
            <form method="POST" action="<?= BASE_URL ?>/index.php?page=appointments&action=updateStatus">
              <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken() ?>">
              <input type="hidden" name="id" value="<?= e($appt['id']) ?>">
              <div class="card-body">
                <div class="form-group">
                  <label>New Status</label>
                  <select name="status" class="form-control">
                    <?php if ($appt['status'] === 'pending'): ?>
                    <option value="confirmed">Confirm</option>
                    <option value="cancelled">Cancel</option>
                    <?php elseif ($appt['status'] === 'confirmed'): ?>
                    <option value="completed">Complete</option>
                    <option value="cancelled">Cancel</option>
                    <?php endif; ?>
                  </select>
                </div>
                <div class="form-group">
                  <label>Doctor Notes (optional)</label>
                  <textarea name="doctor_notes" class="form-control" rows="3"><?= e($appt['doctor_notes'] ?? '') ?></textarea>
                </div>
              </div>
              <div class="card-footer">
                <button type="submit" class="btn btn-primary">Update</button>
              </div>
            </form>
          </div>
          <?php endif; ?>

          <?php if ($prescription): ?>
          <div class="card card-success">
            <div class="card-header"><h3 class="card-title">Prescription</h3></div>
            <div class="card-body">
              <dl class="row">
                <dt class="col-sm-4">Diagnosis</dt>
                <dd class="col-sm-8"><?= e($prescription['diagnosis']) ?></dd>
                <dt class="col-sm-4">Medications</dt>
                <dd class="col-sm-8"><?= e($prescription['medications']) ?></dd>
                <?php if ($prescription['notes']): ?>
                <dt class="col-sm-4">Notes</dt>
                <dd class="col-sm-8"><?= e($prescription['notes']) ?></dd>
                <?php endif; ?>
              </dl>
              <?php if ($prescription['file_path']): ?>
              <a href="<?= BASE_URL ?>/index.php?page=prescriptions&action=download&id=<?= e($prescription['id']) ?>" class="btn btn-sm btn-success">
                <i class="fas fa-download mr-1"></i>Download PDF
              </a>
              <?php endif; ?>
            </div>
          </div>
          <?php elseif ($role === 'doctor' && $appt['status'] === 'completed'): ?>
          <div class="card">
            <div class="card-body">
              <a href="<?= BASE_URL ?>/index.php?page=prescriptions&action=add&appt_id=<?= e($appt['id']) ?>" class="btn btn-warning">
                <i class="fas fa-plus mr-1"></i>Add Prescription
              </a>
            </div>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </section>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
