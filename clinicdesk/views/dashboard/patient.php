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
        <div class="col-sm-6">
          <h1 class="m-0">My Dashboard</h1>
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

      <?php if ($nextAppt): ?>
      <div class="row">
        <div class="col-12">
          <div class="callout callout-info">
            <h5>Next Appointment</h5>
            <p>
              <strong><?= e($nextAppt['doctor_name']) ?></strong> — <?= e($nextAppt['specialization_name']) ?><br>
              <?= e(formatDate($nextAppt['appt_date'])) ?> at <?= e(formatTime($nextAppt['appt_time'])) ?>
              <span class="badge badge-info ml-2"><?= e($nextAppt['status']) ?></span>
            </p>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <div class="row">
        <div class="col-lg-4 col-6">
          <div class="small-box bg-info">
            <div class="inner">
              <h3><?= e($activeCount) ?></h3>
              <p>Active Appointments</p>
            </div>
            <div class="icon"><i class="fas fa-calendar-check"></i></div>
            <a href="<?= BASE_URL ?>/index.php?page=appointments&action=mine" class="small-box-footer">View <i class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>

        <div class="col-lg-4 col-6">
          <div class="small-box bg-success">
            <div class="inner">
              <h3><?= e($completedCount) ?></h3>
              <p>Completed</p>
            </div>
            <div class="icon"><i class="fas fa-check"></i></div>
          </div>
        </div>

        <div class="col-lg-4 col-6">
          <div class="small-box bg-warning">
            <div class="inner">
              <h3><?= e($prescCount) ?></h3>
              <p>Prescriptions</p>
            </div>
            <div class="icon"><i class="fas fa-file-medical"></i></div>
            <a href="<?= BASE_URL ?>/index.php?page=prescriptions&action=mine" class="small-box-footer">View <i class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-12">
          <a href="<?= BASE_URL ?>/index.php?page=appointments&action=book" class="btn btn-primary btn-lg">
            <i class="fas fa-plus-circle mr-2"></i>Book New Appointment
          </a>
        </div>
      </div>

    </div>
  </section>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
