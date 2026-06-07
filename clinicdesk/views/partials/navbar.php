<?php
$user = Auth::currentUser();
?>
<nav class="main-header navbar navbar-expand navbar-white navbar-light">
  <ul class="navbar-nav">
    <li class="nav-item">
      <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
    </li>
    <li class="nav-item d-none d-sm-inline-block">
      <a href="<?= BASE_URL ?>/index.php?page=dashboard" class="nav-link"><?= e(APP_NAME) ?></a>
    </li>
  </ul>

  <ul class="navbar-nav ml-auto">
    <li class="nav-item dropdown">
      <a class="nav-link" data-toggle="dropdown" href="#">
        <i class="far fa-user"></i> <?= e($user['name'] ?? '') ?>
        <span class="badge badge-secondary ml-1"><?= e($user['role'] ?? '') ?></span>
      </a>
      <div class="dropdown-menu dropdown-menu-right">
        <a href="<?= BASE_URL ?>/index.php?page=users&action=profile" class="dropdown-item">
          <i class="fas fa-id-card mr-2"></i> My Profile
        </a>
        <div class="dropdown-divider"></div>
        <form method="POST" action="<?= BASE_URL ?>/index.php?page=auth&action=logout" class="d-inline">
          <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken() ?>">
          <button type="submit" class="dropdown-item text-danger">
            <i class="fas fa-sign-out-alt mr-2"></i> Logout
          </button>
        </form>
      </div>
    </li>
  </ul>
</nav>
