<?php
// Проверяем авторизацию пользователя
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $isLoggedIn ? $_SESSION['user_name'] : '';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Концертное агентство</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Шапка -->
    <header>
        <div class="container">
            <div class="header-content">
                <div class="logo"><?php echo SITE_NAME; ?></div>
                <nav>
                    <ul>
                        <li><a href="index.php">Главная</a></li>
                        <li><a href="index.php?module=events&action=list">Мероприятия</a></li>
                        <li><a href="#about">О нас</a></li>
                        <li><a href="#contacts">Контакты</a></li>
                        <?php if($isLoggedIn && $_SESSION['user_role'] == 'admin'): ?>
                            <li><a href="index.php?module=admin&action=dashboard">Админ-панель</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <div class="auth-buttons">
                    <?php if($isLoggedIn): ?>
                        <span>Добро пожаловать, <?php echo $userName; ?></span>
                        <a href="index.php?module=auth&action=logout" class="logout-btn">Выйти</a>
                    <?php else: ?>
                        <button onclick="window.location.href='index.php?module=auth&action=login'">Войти</button>
                        <button onclick="window.location.href='index.php?module=auth&action=register'">Регистрация</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>