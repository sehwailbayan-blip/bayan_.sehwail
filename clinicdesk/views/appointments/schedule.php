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
        <div class="col-sm-6"><h1 class="m-0">My Schedule</h1></div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/index.php?page=dashboard">Dashboard</a></li>
            <li class="breadcrumb-item active">Schedule</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <section class="content">
    <div class="container-fluid">
      <?php require_once __DIR__ . '/../partials/alerts.php'; ?>

      <?php if (!empty($today)): ?>
      <div class="card card-primary">
        <div class="card-header"><h3 class="card-title">Today's Appointments</h3></div>
        <div class="card-body p-0">
          <table class="table table-striped">
            <thead><tr><th>Patient</th><th>Time</th><th>Reason</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
              <?php foreach ($today as $appt): ?>
              <?php
              $badge = match($appt['status']) {
                  'pending'   => 'warning',
                  'confirmed' => 'info',
                  'completed' => 'success',
                  'cancelled' => 'danger',
                  default     => 'secondary',
              };
              ?>
              <tr>
                <td><?= e($appt['patient_name']) ?></td>
                <td><?= e(formatTime($appt['appt_time'])) ?></td>
                <td><?= e($appt['reason'] ?? '—') ?></td>
                <td><span class="badge badge-<?= $badge ?>"><?= e($appt['status']) ?></span></td>
                <td>
                  <a href="<?= BASE_URL ?>/index.php?page=appointments&action=detail&id=<?= e($appt['id']) ?>" class="btn btn-xs btn-primary">View</a>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
      <?php endif; ?>

      <div class="card">
        <div class="card-header">
          <form method="GET" action="<?= BASE_URL ?>/index.php" class="form-inline">
            <input type="hidden" name="page" value="appointments">
            <input type="hidden" name="action" value="schedule">
            <select name="status" class="form-control form-control-sm mr-2">
              <option value="">All Status</option>
              <?php foreach (['pending','confirmed','completed','cancelled'] as $s): ?>
              <option value="<?= $s ?>" <?= ($_GET['status'] ?? '') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
              <?php endforeach; ?>
            </select>
            <input type="date" name="start_date" class="form-control form-control-sm mr-2" value="<?= e($_GET['start_date'] ?? '') ?>">
            <input type="date" name="end_date" class="form-control form-control-sm mr-2" value="<?= e($_GET['end_date'] ?? '') ?>">
            <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
            <a href="<?= BASE_URL ?>/index.php?page=appointments&action=schedule" class="btn btn-link btn-sm">Clear</a>
          </form>
        </div>

        <div class="card-body p-0">
          <table class="table table-striped">
            <thead><tr><th>Patient</th><th>Date</th><th>Time</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
              <?php foreach ($list as $appt): ?>
              <?php
              $badge = match($appt['status']) {
                  'pending'   => 'warning',
                  'confirmed' => 'info',
                  'completed' => 'success',
                  'cancelled' => 'danger',
                  default     => 'secondary',
              };
              ?>
              <tr>
                <td><?= e($appt['patient_name']) ?></td>
                <td><?= e(formatDate($appt['appt_date'])) ?></td>
                <td><?= e(formatTime($appt['appt_time'])) ?></td>
                <td><span class="badge badge-<?= $badge ?>"><?= e($appt['status']) ?></span></td>
                <td>
                  <a href="<?= BASE_URL ?>/index.php?page=appointments&action=detail&id=<?= e($appt['id']) ?>" class="btn btn-xs btn-primary">View</a>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php if (empty($list)): ?>
              <tr><td colspan="5" class="text-center text-muted">No appointments found.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <?php if ($pager->totalPages() > 1): ?>
        <div class="card-footer">
          <nav>
            <ul class="pagination pagination-sm">
              <?php if ($pager->hasPrev()): ?>
              <li class="page-item"><a class="page-link" href="?page=appointments&action=schedule&p=<?= $pager->currentPage() - 1 ?>">Prev</a></li>
              <?php endif; ?>
              <?php for ($i = 1; $i <= $pager->totalPages(); $i++): ?>
              <li class="page-item <?= $i === $pager->currentPage() ? 'active' : '' ?>">
                <a class="page-link" href="?page=appointments&action=schedule&p=<?= $i ?>"><?= $i ?></a>
              </li>
              <?php endfor; ?>
              <?php if ($pager->hasNext()): ?>
              <li class="page-item"><a class="page-link" href="?page=appointments&action=schedule&p=<?= $pager->currentPage() + 1 ?>">Next</a></li>
              <?php endif; ?>
            </ul>
          </nav>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </section>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
