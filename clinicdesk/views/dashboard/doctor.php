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
        <div class="col-sm-6">
          <h1 class="m-0">Doctor Dashboard</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item active">Dashboard</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <section class="content">
    <div class="container-fluid">

      <?php require_once __DIR__ . '/../partials/alerts.php'; ?>

      <div class="row">
        <div class="col-lg-4 col-6">
          <div class="small-box bg-info">
            <div class="inner">
              <h3><?= e($monthTotal) ?></h3>
              <p>Appointments This Month</p>
            </div>
            <div class="icon"><i class="fas fa-calendar"></i></div>
          </div>
        </div>
        <div class="col-lg-4 col-6">
          <div class="small-box bg-warning">
            <div class="inner">
              <h3><?= e($pendingCount) ?></h3>
              <p>Pending Today</p>
            </div>
            <div class="icon"><i class="fas fa-clock"></i></div>
          </div>
        </div>
        <div class="col-lg-4 col-6">
          <div class="small-box bg-success">
            <div class="inner">
              <h3><?= e($completedCount) ?></h3>
              <p>Completed Today</p>
            </div>
            <div class="icon"><i class="fas fa-check-circle"></i></div>
          </div>
        </div>
      </div>

      <?php if (!empty($todayAppts)): ?>
      <div class="row">
        <div class="col-12">
          <div class="card card-primary">
            <div class="card-header">
              <h3 class="card-title">Today's Appointments</h3>
            </div>
            <div class="card-body p-0">
              <table class="table table-striped">
                <thead>
                  <tr><th>Patient</th><th>Time</th><th>Status</th><th></th></tr>
                </thead>
                <tbody>
                  <?php foreach ($todayAppts as $appt): ?>
                  <tr>
                    <td><?= e($appt['patient_name']) ?></td>
                    <td><?= e(formatTime($appt['appt_time'])) ?></td>
                    <td>
                      <?php
                      $badge = match($appt['status']) {
                          'pending'   => 'warning',
                          'confirmed' => 'info',
                          'completed' => 'success',
                          'cancelled' => 'danger',
                          default     => 'secondary',
                      };
                      ?>
                      <span class="badge badge-<?= $badge ?>"><?= e($appt['status']) ?></span>
                    </td>
                    <td>
                      <a href="<?= BASE_URL ?>/index.php?page=appointments&action=detail&id=<?= e($appt['id']) ?>" class="btn btn-sm btn-outline-primary">View</a>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <?php if (!empty($upcoming)): ?>
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Upcoming Appointments</h3>
            </div>
            <div class="card-body p-0">
              <table class="table table-striped">
                <thead>
                  <tr><th>Patient</th><th>Date</th><th>Time</th><th>Status</th></tr>
                </thead>
                <tbody>
                  <?php foreach ($upcoming as $appt): ?>
                  <tr>
                    <td><?= e($appt['patient_name']) ?></td>
                    <td><?= e(formatDate($appt['appt_date'])) ?></td>
                    <td><?= e(formatTime($appt['appt_time'])) ?></td>
                    <td><span class="badge badge-info"><?= e($appt['status']) ?></span></td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
      <?php endif; ?>

    </div>
  </section>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
