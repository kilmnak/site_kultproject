<?php
require_once '../config.php';
require_once '../includes/functions.php';

session_start();

$auth = new Auth();
$auth->requireRole('admin');

$page = $_GET['page'] ?? 'dashboard';
$action = $_GET['action'] ?? 'list';
$id = intval($_GET['id'] ?? 0);

// Определяем, какую страницу загружать
$allowedPages = ['dashboard', 'events', 'users', 'analytics', 'settings'];
if (!in_array($page, $allowedPages)) {
    $page = 'dashboard';
}

// Загружаем соответствующую страницу
switch ($page) {
    case 'events':
        include 'events.php';
        break;
    case 'users':
        include 'users.php';
        break;
    case 'analytics':
        include 'analytics.php';
        break;
    case 'settings':
        include 'settings.php';
        break;
    default:
        include 'dashboard.php';
        break;
}
?>
