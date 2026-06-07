<?php
require_once __DIR__ . '/../../core/Auth.php';
Auth::requireRole('patient');
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';
require_once __DIR__ . '/../partials/sidebar.php';
?>

<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6"><h1 class="m-0">My Prescriptions</h1></div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/index.php?page=dashboard">Dashboard</a></li>
            <li class="breadcrumb-item active">Prescriptions</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <section class="content">
    <div class="container-fluid">
      <?php require_once __DIR__ . '/../partials/alerts.php'; ?>

      <div class="card">
        <div class="card-header"><h3 class="card-title">Prescription History</h3></div>
        <div class="card-body p-0">
          <table class="table table-striped">
            <thead>
              <tr><th>Doctor</th><th>Date</th><th>Diagnosis</th><th>Download</th></tr>
            </thead>
            <tbody>
              <?php foreach ($list as $presc): ?>
              <tr>
                <td><?= e($presc['doctor_name']) ?></td>
                <td><?= e(formatDate($presc['appt_date'])) ?></td>
                <td><?= e(mb_strimwidth($presc['diagnosis'], 0, 80, '…')) ?></td>
                <td>
                  <?php if ($presc['file_path']): ?>
                  <a href="<?= BASE_URL ?>/index.php?page=prescriptions&action=download&id=<?= e($presc['id']) ?>" class="btn btn-xs btn-success">
                    <i class="fas fa-download"></i> PDF
                  </a>
                  <?php else: ?>
                  <span class="text-muted">—</span>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php if (empty($list)): ?>
              <tr><td colspan="4" class="text-center text-muted">No prescriptions yet.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </section>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
