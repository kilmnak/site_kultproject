<?php
// Конфигурация базы данных
define('DB_HOST', 'localhost');
define('DB_NAME', 'kultproject');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Настройки сайта
define('SITE_NAME', 'КультПросвет');
define('SITE_URL', 'http://localhost');
define('ADMIN_EMAIL', 'admin@kultproject.ru');

// Настройки безопасности
define('SECRET_KEY', 'your-secret-key-here');
define('SESSION_LIFETIME', 3600); // 1 час

// Настройки первого администратора
define('FIRST_ADMIN_EMAIL', 'admin@kultproject.ru');
define('FIRST_ADMIN_PASSWORD', 'admin123'); // Измените пароль после первого входа
define('FIRST_ADMIN_FIRST_NAME', 'Администратор');
define('FIRST_ADMIN_LAST_NAME', 'Системы');

// Настройки платежей
define('PAYMENT_GATEWAY_URL', 'https://payment.example.com');
define('PAYMENT_API_KEY', 'your-payment-api-key');

// Настройки уведомлений
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your-email@gmail.com');
define('SMTP_PASS', 'your-app-password');

// Настройки файлов
define('UPLOAD_PATH', 'uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// Настройки бронирования
define('BOOKING_TIMEOUT', 15 * 60); // 15 минут в секундах

// Часовой пояс
date_default_timezone_set('Europe/Moscow');

// Обработка ошибок
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Автозагрузка классов
spl_autoload_register(function ($class) {
    $file = __DIR__ . '/classes/' . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});
?>