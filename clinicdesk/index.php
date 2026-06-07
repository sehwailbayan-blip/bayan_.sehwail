<?php
session_start();

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/core/Auth.php';
require_once __DIR__ . '/core/CSRF.php';
require_once __DIR__ . '/core/helpers.php';

$page   = $_GET['page']   ?? 'auth';
$action = $_GET['action'] ?? 'index';

$routes = [
    'auth'            => ['controller' => 'AuthController',           'file' => 'controllers/AuthController.php'],
    'dashboard'       => ['controller' => 'DashboardController',      'file' => 'controllers/DashboardController.php'],
    'users'           => ['controller' => 'UserController',           'file' => 'controllers/UserController.php'],
    'doctors'         => ['controller' => 'DoctorController',         'file' => 'controllers/DoctorController.php'],
    'appointments'    => ['controller' => 'AppointmentController',    'file' => 'controllers/AppointmentController.php'],
    'prescriptions'   => ['controller' => 'PrescriptionController',   'file' => 'controllers/PrescriptionController.php'],
    'reports'         => ['controller' => 'ReportController',         'file' => 'controllers/ReportController.php'],
    'specializations' => ['controller' => 'SpecializationController', 'file' => 'controllers/SpecializationController.php'],
];

if (!isset($routes[$page])) {
    http_response_code(404);
    require_once __DIR__ . '/views/errors/404.php';
    exit;
}

require_once __DIR__ . '/' . $routes[$page]['file'];
$controller = new $routes[$page]['controller']();

$methodMap = [
    'auth' => [
        'login'       => 'login',
        'handleLogin' => 'handleLogin',
        'logout'      => 'logout',
        'index'       => 'login',
    ],
    'dashboard' => [
        'index' => 'index',
    ],
    'users' => [
        'index'         => 'index',
        'create'        => 'create',
        'store'         => 'store',
        'edit'          => 'edit',
        'update'        => 'update',
        'toggleActive'  => 'toggleActive',
        'profile'       => 'profile',
        'updateProfile' => 'updateProfile',
    ],
    'doctors' => [
        'index'  => 'index',
        'edit'   => 'edit',
        'update' => 'update',
    ],
    'appointments' => [
        'index'        => 'all',
        'book'         => 'book',
        'store'        => 'store',
        'mine'         => 'mine',
        'schedule'     => 'schedule',
        'all'          => 'all',
        'detail'       => 'detail',
        'updateStatus' => 'updateStatus',
        'cancel'       => 'cancel',
    ],
    'prescriptions' => [
        'index'  => 'myPrescriptions',
        'add'    => 'add',
        'store'  => 'store',
        'mine'   => 'myPrescriptions',
        'download' => 'download',
    ],
    'reports' => [
        'index' => 'index',
    ],
    'specializations' => [
        'index'  => 'index',
        'store'  => 'store',
        'delete' => 'delete',
    ],
];

$method = $methodMap[$page][$action] ?? null;

if (!$method || !method_exists($controller, $method)) {
    http_response_code(404);
    require_once __DIR__ . '/views/errors/404.php';
    exit;
}

$controller->$method();
