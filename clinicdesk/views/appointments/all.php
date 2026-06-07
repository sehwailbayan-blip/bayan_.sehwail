<?php
require_once __DIR__ . '/../../core/Auth.php';
Auth::requireRole('admin');
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';
require_once __DIR__ . '/../partials/sidebar.php';
?>

<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6"><h1 class="m-0">All Appointments</h1></div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/index.php?page=dashboard">Dashboard</a></li>
            <li class="breadcrumb-item active">Appointments</li>
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
          <form method="GET" action="<?= BASE_URL ?>/index.php" class="form-inline flex-wrap">
            <input type="hidden" name="page" value="appointments">
            <input type="hidden" name="action" value="all">
            <select name="doctor_id" class="form-control form-control-sm mr-2 mb-1">
              <option value="">All Doctors</option>
              <?php foreach ($doctors as $doc): ?>
              <option value="<?= e($doc['id']) ?>" <?= ((int)($_GET['doctor_id'] ?? 0)) === (int)$doc['id'] ? 'selected' : '' ?>>
                <?= e($doc['name']) ?>
              </option>
              <?php endforeach; ?>
            </select>
            <input type="text" name="patient_name" placeholder="Patient name" class="form-control form-control-sm mr-2 mb-1" value="<?= e($_GET['patient_name'] ?? '') ?>">
            <select name="status" class="form-control form-control-sm mr-2 mb-1">
              <option value="">All Status</option>
              <?php foreach (['pending','confirmed','completed','cancelled'] as $s): ?>
              <option value="<?= $s ?>" <?= ($_GET['status'] ?? '') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
              <?php endforeach; ?>
            </select>
            <input type="date" name="start_date" class="form-control form-control-sm mr-2 mb-1" value="<?= e($_GET['start_date'] ?? '') ?>">
            <input type="date" name="end_date" class="form-control form-control-sm mr-2 mb-1" value="<?= e($_GET['end_date'] ?? '') ?>">
            <button type="submit" class="btn btn-secondary btn-sm mb-1">Filter</button>
            <a href="<?= BASE_URL ?>/index.php?page=appointments" class="btn btn-link btn-sm mb-1">Clear</a>
          </form>
        </div>

        <div class="card-body p-0">
          <table class="table table-striped table-sm">
            <thead>
              <tr>
                <th>Patient</th><th>Doctor</th><th>Date</th><th>Time</th><th>Status</th><th>Reason</th><th></th>
              </tr>
            </thead>
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
                <td><?= e($appt['doctor_name']) ?></td>
                <td><?= e(formatDate($appt['appt_date'])) ?></td>
                <td><?= e(formatTime($appt['appt_time'])) ?></td>
                <td><span class="badge badge-<?= $badge ?>"><?= e($appt['status']) ?></span></td>
                <td><?= e($appt['reason'] ?? '—') ?></td>
                <td>
                  <a href="<?= BASE_URL ?>/index.php?page=appointments&action=detail&id=<?= e($appt['id']) ?>" class="btn btn-xs btn-primary">View</a>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php if (empty($list)): ?>
              <tr><td colspan="7" class="text-center text-muted">No appointments found.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <?php if ($pager->totalPages() > 1): ?>
        <div class="card-footer">
          <nav><ul class="pagination pagination-sm">
            <?php if ($pager->hasPrev()): ?>
            <li class="page-item"><a class="page-link" href="?page=appointments&action=all&p=<?= $pager->currentPage() - 1 ?>">Prev</a></li>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $pager->totalPages(); $i++): ?>
            <li class="page-item <?= $i === $pager->currentPage() ? 'active' : '' ?>">
              <a class="page-link" href="?page=appointments&action=all&p=<?= $i ?>"><?= $i ?></a>
            </li>
            <?php endfor; ?>
            <?php if ($pager->hasNext()): ?>
            <li class="page-item"><a class="page-link" href="?page=appointments&action=all&p=<?= $pager->currentPage() + 1 ?>">Next</a></li>
            <?php endif; ?>
          </ul></nav>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </section>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
