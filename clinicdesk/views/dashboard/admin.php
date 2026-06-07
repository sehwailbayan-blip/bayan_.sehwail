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
        <div class="col-sm-6">
          <h1 class="m-0">Admin Dashboard</h1>
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
        <div class="col-lg-3 col-6">
          <div class="small-box bg-info">
            <div class="inner">
              <h3><?= e($roleCounts['doctor'] ?? 0) ?></h3>
              <p>Doctors</p>
            </div>
            <div class="icon"><i class="fas fa-user-md"></i></div>
            <a href="<?= BASE_URL ?>/index.php?page=doctors" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>

        <div class="col-lg-3 col-6">
          <div class="small-box bg-success">
            <div class="inner">
              <h3><?= e($roleCounts['patient'] ?? 0) ?></h3>
              <p>Patients</p>
            </div>
            <div class="icon"><i class="fas fa-users"></i></div>
            <a href="<?= BASE_URL ?>/index.php?page=users&role=patient" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>

        <div class="col-lg-3 col-6">
          <div class="small-box bg-warning">
            <div class="inner">
              <h3><?= e($today) ?></h3>
              <p>Appointments Today</p>
            </div>
            <div class="icon"><i class="fas fa-calendar-day"></i></div>
            <a href="<?= BASE_URL ?>/index.php?page=appointments" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>

        <div class="col-lg-3 col-6">
          <div class="small-box bg-danger">
            <div class="inner">
              <h3><?= e($weekStats['pending'] ?? 0) ?></h3>
              <p>Pending This Week</p>
            </div>
            <div class="icon"><i class="fas fa-clock"></i></div>
            <a href="<?= BASE_URL ?>/index.php?page=appointments&status=pending" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Recent Appointments</h3>
            </div>
            <div class="card-body p-0">
              <table class="table table-striped">
                <thead>
                  <tr>
                    <th>Patient</th>
                    <th>Doctor</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($recent as $appt): ?>
                  <tr>
                    <td><?= e($appt['patient_name']) ?></td>
                    <td><?= e($appt['doctor_name']) ?></td>
                    <td><?= e(formatDate($appt['appt_date'])) ?></td>
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
                  </tr>
                  <?php endforeach; ?>
                  <?php if (empty($recent)): ?>
                  <tr><td colspan="5" class="text-center text-muted">No appointments yet.</td></tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

    </div>
  </section>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
