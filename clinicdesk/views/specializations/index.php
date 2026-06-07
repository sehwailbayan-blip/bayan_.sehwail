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
        <div class="col-sm-6"><h1 class="m-0">Specializations</h1></div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/index.php?page=dashboard">Dashboard</a></li>
            <li class="breadcrumb-item active">Specializations</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <section class="content">
    <div class="container-fluid">
      <?php require_once __DIR__ . '/../partials/alerts.php'; ?>

      <div class="row">
        <div class="col-md-4">
          <div class="card">
            <div class="card-header"><h3 class="card-title">Add Specialization</h3></div>
            <form method="POST" action="<?= BASE_URL ?>/index.php?page=specializations&action=store">
              <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken() ?>">
              <div class="card-body">
                <div class="form-group">
                  <label>Name</label>
                  <input type="text" name="name" class="form-control" required>
                </div>
              </div>
              <div class="card-footer">
                <button type="submit" class="btn btn-primary btn-sm">Add</button>
              </div>
            </form>
          </div>
        </div>

        <div class="col-md-8">
          <div class="card">
            <div class="card-header"><h3 class="card-title">All Specializations</h3></div>
            <div class="card-body p-0">
              <table class="table table-striped">
                <thead><tr><th>#</th><th>Name</th><th>Actions</th></tr></thead>
                <tbody>
                  <?php foreach ($list as $spec): ?>
                  <tr>
                    <td><?= e($spec['id']) ?></td>
                    <td><?= e($spec['name']) ?></td>
                    <td>
                      <form method="POST" action="<?= BASE_URL ?>/index.php?page=specializations&action=delete" class="d-inline">
                        <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken() ?>">
                        <input type="hidden" name="id" value="<?= e($spec['id']) ?>">
                        <button type="submit" class="btn btn-xs btn-danger" onclick="return confirm('Delete this specialization?')">Delete</button>
                      </form>
                    </td>
                  </tr>
                  <?php endforeach; ?>
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
