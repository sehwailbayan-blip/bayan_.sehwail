<?php
require_once __DIR__ . '/../../core/Auth.php';
Auth::requireRole('admin', 'doctor');
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';
require_once __DIR__ . '/../partials/sidebar.php';

$availDays = explode(',', $doctor['available_days'] ?? '');
?>

<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6"><h1 class="m-0">Edit Doctor Profile</h1></div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/index.php?page=doctors">Doctors</a></li>
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
        <div class="card-header"><h3 class="card-title"><?= e($doctor['name']) ?></h3></div>
        <form method="POST" action="<?= BASE_URL ?>/index.php?page=doctors&action=update" enctype="multipart/form-data">
          <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken() ?>">
          <input type="hidden" name="doctor_id" value="<?= e($doctor['id']) ?>">
          <div class="card-body">
            <div class="form-group">
              <label>Specialization</label>
              <select name="specialization_id" class="form-control">
                <?php foreach ($specs as $spec): ?>
                <option value="<?= e($spec['id']) ?>" <?= (int)$spec['id'] === (int)$doctor['specialization_id'] ? 'selected' : '' ?>>
                  <?= e($spec['name']) ?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label>Bio</label>
              <textarea name="bio" class="form-control" rows="4"><?= e($doctor['bio'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
              <label>Consultation Fee ($)</label>
              <input type="number" step="0.01" name="consultation_fee" class="form-control" value="<?= e($doctor['consultation_fee']) ?>">
            </div>
            <div class="form-group">
              <label>Available Days</label><br>
              <?php foreach (['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $day): ?>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" name="available_days[]" value="<?= $day ?>"
                  <?= in_array($day, $availDays) ? 'checked' : '' ?>>
                <label class="form-check-label"><?= $day ?></label>
              </div>
              <?php endforeach; ?>
            </div>
            <div class="form-group">
              <label>Profile Photo (JPEG/PNG, max 1MB)</label>
              <input type="file" name="photo" class="form-control-file">
              <?php if ($doctor['avatar']): ?>
              <img src="<?= BASE_URL ?>/public/uploads/doctor_photos/<?= e($doctor['avatar']) ?>" width="60" class="mt-2 rounded">
              <?php endif; ?>
            </div>
          </div>
          <div class="card-footer">
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="<?= BASE_URL ?>/index.php?page=doctors" class="btn btn-secondary">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </section>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
