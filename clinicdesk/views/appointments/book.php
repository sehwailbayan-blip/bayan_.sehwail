<?php
require_once __DIR__ . '/../../core/Auth.php';
Auth::requireRole('patient');
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';
require_once __DIR__ . '/../partials/sidebar.php';

$times = ['09:00','09:30','10:00','10:30','11:00','11:30','12:00','12:30',
          '13:00','13:30','14:00','14:30','15:00','15:30','16:00'];
?>

<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6"><h1 class="m-0">Book Appointment</h1></div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/index.php?page=dashboard">Dashboard</a></li>
            <li class="breadcrumb-item active">Book</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <section class="content">
    <div class="container-fluid">
      <?php require_once __DIR__ . '/../partials/alerts.php'; ?>

      <div class="card">
        <div class="card-header"><h3 class="card-title">New Appointment</h3></div>
        <form method="POST" action="<?= BASE_URL ?>/index.php?page=appointments&action=store">
          <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken() ?>">
          <div class="card-body">
            <div class="form-group">
              <label>Doctor</label>
              <select name="doctor_id" id="doctorSelect" class="form-control" required>
                <option value="">— Select Doctor —</option>
                <?php foreach ($doctors as $doc): ?>
                <option value="<?= e($doc['id']) ?>"
                  data-days="<?= e($doc['available_days']) ?>">
                  <?= e($doc['name']) ?> — <?= e($doc['specialization_name']) ?> ($<?= e(number_format($doc['consultation_fee'], 2)) ?>)
                </option>
                <?php endforeach; ?>
              </select>
              <small id="availDaysInfo" class="form-text text-muted"></small>
            </div>

            <div class="form-group">
              <label>Date</label>
              <input type="date" name="appt_date" class="form-control" min="<?= date('Y-m-d') ?>" required>
            </div>

            <div class="form-group">
              <label>Time Slot</label>
              <select name="appt_time" class="form-control" required>
                <option value="">— Select Time —</option>
                <?php foreach ($times as $t): ?>
                <option value="<?= $t ?>"><?= date('h:i A', strtotime($t)) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="form-group">
              <label>Reason (optional)</label>
              <input type="text" name="reason" class="form-control" maxlength="255">
            </div>
          </div>
          <div class="card-footer">
            <button type="submit" class="btn btn-primary">Book Appointment</button>
            <a href="<?= BASE_URL ?>/index.php?page=appointments&action=mine" class="btn btn-secondary">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </section>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
<script>
document.getElementById('doctorSelect').addEventListener('change', function () {
  const opt  = this.options[this.selectedIndex];
  const days = opt.getAttribute('data-days') || '';
  const info = document.getElementById('availDaysInfo');
  info.textContent = days ? 'Available: ' + days : '';
});
</script>
