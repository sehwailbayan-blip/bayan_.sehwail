<?php if (!empty($_SESSION['flash'])): ?>
<?php
$flash = $_SESSION['flash'];
unset($_SESSION['flash']);
$alertClass = match($flash['type']) {
    'success' => 'alert-success',
    'error'   => 'alert-danger',
    'warning' => 'alert-warning',
    default   => 'alert-info',
};
?>
<div class="alert <?= $alertClass ?> alert-dismissible fade show mx-3 mt-3" role="alert">
  <?= e($flash['message']) ?>
  <button type="button" class="close" data-dismiss="alert">
    <span>&times;</span>
  </button>
</div>
<?php endif; ?>
