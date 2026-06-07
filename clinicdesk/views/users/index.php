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
        <div class="col-sm-6"><h1 class="m-0">Manage Users</h1></div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/index.php?page=dashboard">Dashboard</a></li>
            <li class="breadcrumb-item active">Users</li>
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
          <div class="d-flex justify-content-between align-items-center">
            <h3 class="card-title">Users List</h3>
            <a href="<?= BASE_URL ?>/index.php?page=users&action=create" class="btn btn-primary btn-sm">
              <i class="fas fa-plus"></i> Add User
            </a>
          </div>
        </div>

        <div class="card-body">
          <form method="GET" action="<?= BASE_URL ?>/index.php" class="form-inline mb-3">
            <input type="hidden" name="page" value="users">
            <select name="role" class="form-control mr-2">
              <option value="">All Roles</option>
              <option value="admin" <?= ($_GET['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
              <option value="doctor" <?= ($_GET['role'] ?? '') === 'doctor' ? 'selected' : '' ?>>Doctor</option>
              <option value="patient" <?= ($_GET['role'] ?? '') === 'patient' ? 'selected' : '' ?>>Patient</option>
            </select>
            <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
          </form>

          <table class="table table-bordered table-striped data-table">
            <thead>
              <tr>
                <th>#</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($usersList as $u): ?>
              <tr>
                <td><?= e($u['id']) ?></td>
                <td><?= e($u['name']) ?></td>
                <td><?= e($u['email']) ?></td>
                <td><span class="badge badge-secondary"><?= e($u['role']) ?></span></td>
                <td>
                  <?php if ($u['is_active']): ?>
                    <span class="badge badge-success">Active</span>
                  <?php else: ?>
                    <span class="badge badge-danger">Suspended</span>
                  <?php endif; ?>
                </td>
                <td>
                  <a href="<?= BASE_URL ?>/index.php?page=users&action=edit&id=<?= e($u['id']) ?>" class="btn btn-xs btn-info">Edit</a>
                  <?php $current = Auth::currentUser(); ?>
                  <?php if ((int)$u['id'] !== (int)$current['id']): ?>
                  <form method="POST" action="<?= BASE_URL ?>/index.php?page=users&action=toggleActive" class="d-inline">
                    <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken() ?>">
                    <input type="hidden" name="id" value="<?= e($u['id']) ?>">
                    <button type="submit" class="btn btn-xs <?= $u['is_active'] ? 'btn-warning' : 'btn-success' ?>">
                      <?= $u['is_active'] ? 'Suspend' : 'Activate' ?>
                    </button>
                  </form>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>

          <?php if ($paginator->totalPages() > 1): ?>
          <nav>
            <ul class="pagination pagination-sm">
              <?php if ($paginator->hasPrev()): ?>
              <li class="page-item">
                <a class="page-link" href="?page=users&p=<?= $paginator->currentPage() - 1 ?>&role=<?= e($_GET['role'] ?? '') ?>">Prev</a>
              </li>
              <?php endif; ?>
              <?php for ($i = 1; $i <= $paginator->totalPages(); $i++): ?>
              <li class="page-item <?= $i === $paginator->currentPage() ? 'active' : '' ?>">
                <a class="page-link" href="?page=users&p=<?= $i ?>&role=<?= e($_GET['role'] ?? '') ?>"><?= $i ?></a>
              </li>
              <?php endfor; ?>
              <?php if ($paginator->hasNext()): ?>
              <li class="page-item">
                <a class="page-link" href="?page=users&p=<?= $paginator->currentPage() + 1 ?>&role=<?= e($_GET['role'] ?? '') ?>">Next</a>
              </li>
              <?php endif; ?>
            </ul>
          </nav>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </section>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
