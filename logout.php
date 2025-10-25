<?php
// logout.php - Выход из системы
session_start();

// Очищаем все переменные сессии
$_SESSION = array();

// Уничтожаем сессию
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

// Перенаправляем на страницу логина
header('Location: /admin-login.php?logout=1');
exit;
?>