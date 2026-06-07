<?php
require_once __DIR__ . '/../../core/Auth.php';
Auth::requireRole('admin');
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';
require_once __DIR__ . '/../partials/sidebar.php';

$statusCounts = [];
foreach ($results as $row) {
    $statusCounts[$row['status']] = ($statusCounts[$row['status']] ?? 0) + 1;
}
?>

<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6"><h1 class="m-0">Reports</h1></div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/index.php?page=dashboard">Dashboard</a></li>
            <li class="breadcrumb-item active">Reports</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <section class="content">
    <div class="container-fluid">
      <?php require_once __DIR__ . '/../partials/alerts.php'; ?>

      <div class="card">
        <div class="card-header"><h3 class="card-title">Filter Report</h3></div>
        <form method="GET" action="<?= BASE_URL ?>/index.php">
          <input type="hidden" name="page" value="reports">
          <div class="card-body">
            <div class="row">
              <div class="col-md-3 form-group">
                <label>Start Date <span class="text-danger">*</span></label>
                <input type="date" name="start_date" class="form-control" value="<?= e($_GET['start_date'] ?? '') ?>" required>
              </div>
              <div class="col-md-3 form-group">
                <label>End Date <span class="text-danger">*</span></label>
                <input type="date" name="end_date" class="form-control" value="<?= e($_GET['end_date'] ?? '') ?>" required>
              </div>
              <div class="col-md-3 form-group">
                <label>Doctor</label>
                <select name="doctor_id" class="form-control">
                  <option value="">All Doctors</option>
                  <?php foreach ($doctors as $doc): ?>
                  <option value="<?= e($doc['id']) ?>" <?= ((int)($_GET['doctor_id'] ?? 0)) === (int)$doc['id'] ? 'selected' : '' ?>>
                    <?= e($doc['name']) ?>
                  </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-3 form-group">
                <label>Status</label>
                <select name="status" class="form-control">
                  <option value="">All</option>
                  <?php foreach (['pending','confirmed','completed','cancelled'] as $s): ?>
                  <option value="<?= $s ?>" <?= ($_GET['status'] ?? '') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
          </div>
          <div class="card-footer">
            <button type="submit" class="btn btn-primary">Generate</button>
            <?php if (!empty($results)): ?>
            <a href="<?= BASE_URL ?>/index.php?page=reports&start_date=<?= e($_GET['start_date'] ?? '') ?>&end_date=<?= e($_GET['end_date'] ?? '') ?>&doctor_id=<?= e($_GET['doctor_id'] ?? '') ?>&status=<?= e($_GET['status'] ?? '') ?>&export=csv"
               class="btn btn-success ml-2">
              <i class="fas fa-download mr-1"></i>Export CSV
            </a>
            <?php endif; ?>
          </div>
        </form>
      </div>

      <?php if (!empty($results)): ?>
      <div class="card">
        <div class="card-header"><h3 class="card-title">Results (<?= count($results) ?> records)</h3></div>
        <div class="card-body p-0">
          <table class="table table-sm table-striped">
            <thead>
              <tr>
                <th>Patient</th><th>Doctor</th><th>Specialization</th><th>Date</th><th>Time</th><th>Status</th><th>Reason</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($results as $row): ?>
              <?php
              $badge = match($row['status']) {
                  'pending'   => 'warning',
                  'confirmed' => 'info',
                  'completed' => 'success',
                  'cancelled' => 'danger',
                  default     => 'secondary',
              };
              ?>
              <tr>
                <td><?= e($row['patient_name']) ?></td>
                <td><?= e($row['doctor_name']) ?></td>
                <td><?= e($row['specialization_name']) ?></td>
                <td><?= e(formatDate($row['appt_date'])) ?></td>
                <td><?= e(formatTime($row['appt_time'])) ?></td>
                <td><span class="badge badge-<?= $badge ?>"><?= e($row['status']) ?></span></td>
                <td><?= e($row['reason'] ?? '—') ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
            <tfoot>
              <tr class="table-secondary font-weight-bold">
                <td colspan="5">Total: <?= count($results) ?></td>
                <td colspan="2">
                  <?php foreach ($statusCounts as $st => $cnt): ?>
                  <span class="mr-2"><?= ucfirst(e($st)) ?>: <?= $cnt ?></span>
                  <?php endforeach; ?>
                </td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
      <?php elseif (!empty($_GET['start_date'])): ?>
      <div class="alert alert-info">No records found for the selected filters.</div>
      <?php endif; ?>

    </div>
  </section>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
