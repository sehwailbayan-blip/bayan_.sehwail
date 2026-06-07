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
        <div class="col-sm-6"><h1 class="m-0">Manage Doctors</h1></div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/index.php?page=dashboard">Dashboard</a></li>
            <li class="breadcrumb-item active">Doctors</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <section class="content">
    <div class="container-fluid">
      <?php require_once __DIR__ . '/../partials/alerts.php'; ?>

      <div class="card">
        <div class="card-header"><h3 class="card-title">Doctors List</h3></div>
        <div class="card-body p-0">
          <table class="table table-striped">
            <thead>
              <tr>
                <th>Name</th><th>Specialization</th><th>Fee</th><th>Available Days</th><th>Status</th><th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($list as $doc): ?>
              <tr>
                <td><?= e($doc['name']) ?></td>
                <td><?= e($doc['specialization_name']) ?></td>
                <td>$<?= e(number_format($doc['consultation_fee'], 2)) ?></td>
                <td><?= e($doc['available_days']) ?></td>
                <td>
                  <?php if ($doc['is_active']): ?>
                    <span class="badge badge-success">Active</span>
                  <?php else: ?>
                    <span class="badge badge-danger">Inactive</span>
                  <?php endif; ?>
                </td>
                <td>
                  <a href="<?= BASE_URL ?>/index.php?page=doctors&action=edit&id=<?= e($doc['id']) ?>" class="btn btn-xs btn-info">Edit</a>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <?php if ($paginator->totalPages() > 1): ?>
        <div class="card-footer">
          <nav>
            <ul class="pagination pagination-sm">
              <?php if ($paginator->hasPrev()): ?>
              <li class="page-item">
                <a class="page-link" href="?page=doctors&p=<?= $paginator->currentPage() - 1 ?>">Prev</a>
              </li>
              <?php endif; ?>
              <?php for ($i = 1; $i <= $paginator->totalPages(); $i++): ?>
              <li class="page-item <?= $i === $paginator->currentPage() ? 'active' : '' ?>">
                <a class="page-link" href="?page=doctors&p=<?= $i ?>"><?= $i ?></a>
              </li>
              <?php endfor; ?>
              <?php if ($paginator->hasNext()): ?>
              <li class="page-item">
                <a class="page-link" href="?page=doctors&p=<?= $paginator->currentPage() + 1 ?>">Next</a>
              </li>
              <?php endif; ?>
            </ul>
          </nav>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </section>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
