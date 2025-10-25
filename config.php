<?php
// Конфигурация сайта
define('SITE_NAME', 'КультПросвет');
define('SITE_URL', 'http://localhost/kultprosvet');
define('DB_HOST', '92.255.109.86');
define('DB_NAME', 'kultprosvet');
define('DB_USER', 'kultprosvet');
define('DB_PASS', 'v9OE2iZqa=&rUf');

// Старт сессии
session_start();

// Подключение к базе данных
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET NAMES utf8mb4");
} catch(PDOException $e) {
    // Если база данных не существует, создаем ее
    if($e->getCode() == 1049) {
        $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
        $pdo->exec("CREATE DATABASE " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE " . DB_NAME);
        
        // Выполняем SQL файл для создания таблиц
        $sql = file_get_contents('database.sql');
        $pdo->exec($sql);
    } else {
        die("Ошибка подключения к базе данных: " . $e->getMessage());
    }
}

// Подключаем функции
require_once 'includes/database.php';
$db = new Database();
?>