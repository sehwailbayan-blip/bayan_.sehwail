<?php
$role        = Auth::role();
$currentPage = $_GET['page']   ?? '';
$currentAction = $_GET['action'] ?? '';

function sidebarActive(string $page, string $action = ''): string {
    global $currentPage, $currentAction;
    if ($action) {
        return ($currentPage === $page && $currentAction === $action) ? 'active' : '';
    }
    return $currentPage === $page ? 'active' : '';
}
?>
<aside class="main-sidebar sidebar-dark-primary elevation-4">
  <a href="<?= BASE_URL ?>/index.php?page=dashboard" class="brand-link">
    <i class="fas fa-clinic-medical brand-image ml-3"></i>
    <span class="brand-text font-weight-light"><?= e(APP_NAME) ?></span>
  </a>

  <div class="sidebar">
    <nav class="mt-2">
      <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">

        <li class="nav-item">
          <a href="<?= BASE_URL ?>/index.php?page=dashboard" class="nav-link <?= sidebarActive('dashboard') ?>">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>Dashboard</p>
          </a>
        </li>

        <?php if ($role === 'admin'): ?>
        <li class="nav-header">ADMINISTRATION</li>

        <li class="nav-item">
          <a href="<?= BASE_URL ?>/index.php?page=users" class="nav-link <?= sidebarActive('users') ?>">
            <i class="nav-icon fas fa-users"></i>
            <p>Users</p>
          </a>
        </li>

        <li class="nav-item">
          <a href="<?= BASE_URL ?>/index.php?page=doctors" class="nav-link <?= sidebarActive('doctors') ?>">
            <i class="nav-icon fas fa-user-md"></i>
            <p>Doctors</p>
          </a>
        </li>

        <li class="nav-item">
          <a href="<?= BASE_URL ?>/index.php?page=specializations" class="nav-link <?= sidebarActive('specializations') ?>">
            <i class="nav-icon fas fa-stethoscope"></i>
            <p>Specializations</p>
          </a>
        </li>

        <li class="nav-item">
          <a href="<?= BASE_URL ?>/index.php?page=appointments" class="nav-link <?= sidebarActive('appointments') ?>">
            <i class="nav-icon fas fa-calendar-check"></i>
            <p>Appointments</p>
          </a>
        </li>

        <li class="nav-item">
          <a href="<?= BASE_URL ?>/index.php?page=reports" class="nav-link <?= sidebarActive('reports') ?>">
            <i class="nav-icon fas fa-chart-bar"></i>
            <p>Reports</p>
          </a>
        </li>
        <?php endif; ?>

        <?php if ($role === 'doctor'): ?>
        <li class="nav-header">DOCTOR</li>

        <li class="nav-item">
          <a href="<?= BASE_URL ?>/index.php?page=appointments&action=schedule" class="nav-link <?= sidebarActive('appointments', 'schedule') ?>">
            <i class="nav-icon fas fa-calendar-alt"></i>
            <p>My Schedule</p>
          </a>
        </li>
        <?php endif; ?>

        <?php if ($role === 'patient'): ?>
        <li class="nav-header">PATIENT</li>

        <li class="nav-item">
          <a href="<?= BASE_URL ?>/index.php?page=appointments&action=book" class="nav-link <?= sidebarActive('appointments', 'book') ?>">
            <i class="nav-icon fas fa-plus-circle"></i>
            <p>Book Appointment</p>
          </a>
        </li>

        <li class="nav-item">
          <a href="<?= BASE_URL ?>/index.php?page=appointments&action=mine" class="nav-link <?= sidebarActive('appointments', 'mine') ?>">
            <i class="nav-icon fas fa-list-alt"></i>
            <p>My Appointments</p>
          </a>
        </li>

        <li class="nav-item">
          <a href="<?= BASE_URL ?>/index.php?page=prescriptions&action=mine" class="nav-link <?= sidebarActive('prescriptions', 'mine') ?>">
            <i class="nav-icon fas fa-file-medical"></i>
            <p>My Prescriptions</p>
          </a>
        </li>
        <?php endif; ?>

      </ul>
    </nav>
  </div>
</aside>
