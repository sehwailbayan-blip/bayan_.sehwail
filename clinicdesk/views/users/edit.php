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
        <div class="col-sm-6"><h1 class="m-0">Edit User</h1></div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/index.php?page=users">Users</a></li>
            <li class="breadcrumb-item active">Edit</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <section class="content">
    <div class="container-fluid">
      <?php require_once __DIR__ . '/../partials/alerts.php'; ?>

      <div class="card">
        <div class="card-header"><h3 class="card-title">Edit: <?= e($user['name']) ?></h3></div>
        <form method="POST" action="<?= BASE_URL ?>/index.php?page=users&action=update" enctype="multipart/form-data">
          <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken() ?>">
          <input type="hidden" name="id" value="<?= e($user['id']) ?>">
          <div class="card-body">
            <div class="form-group">
              <label>Full Name</label>
              <input type="text" name="name" class="form-control" value="<?= e($user['name']) ?>" required>
            </div>
            <div class="form-group">
              <label>Phone</label>
              <input type="text" name="phone" class="form-control" value="<?= e($user['phone'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label>Avatar (JPEG/PNG, max 1MB)</label>
              <input type="file" name="avatar" class="form-control-file">
              <?php if ($user['avatar']): ?>
              <img src="<?= BASE_URL ?>/public/uploads/avatars/<?= e($user['avatar']) ?>" width="60" class="mt-2 rounded">
              <?php endif; ?>
            </div>
          </div>
          <div class="card-footer">
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="<?= BASE_URL ?>/index.php?page=users" class="btn btn-secondary">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </section>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
