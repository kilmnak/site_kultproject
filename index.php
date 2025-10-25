<?php
require_once 'config.php';

// Получаем параметры из URL
$module = $_GET['module'] ?? 'main';
$action = $_GET['action'] ?? 'index';

// Подключаем шапку
include 'header.php';

// Маршрутизация
switch($module) {
    case 'main':
        include 'modules/main.php';
        break;
        
    case 'auth':
        if($action == 'login') {
            include 'modules/auth/login.php';
        } elseif($action == 'register') {
            include 'modules/auth/register.php';
        } elseif($action == 'logout') {
            include 'modules/auth/logout.php';
        }
        break;
        
    case 'events':
        if($action == 'list') {
            include 'modules/events/list.php';
        } elseif($action == 'details') {
            include 'modules/events/details.php';
        }
        break;
        
    case 'tickets':
        if($action == 'purchase') {
            include 'modules/tickets/purchase.php';
        }
        break;
        
    case 'admin':
        if($action == 'dashboard') {
            include 'modules/admin/dashboard.php';
        } elseif($action == 'events_management') {
            include 'modules/admin/events_management.php';
        }
        break;
        
    default:
        include 'modules/main.php';
        break;
}

// Подключаем подвал
include 'footer.php';
?>