<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' . SITE_NAME : SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="/">
                <i class="fas fa-theater-masks me-2"></i>
                <?php echo SITE_NAME; ?>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/">Главная</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/events.php">Мероприятия</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/about.php">О нас</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/contact.php">Контакты</a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i>
                                <?php echo $_SESSION['user_name']; ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="/profile.php">Личный кабинет</a></li>
                                <li><a class="dropdown-item" href="/my-tickets.php">Мои билеты</a></li>
                                <?php if ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'manager'): ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="/admin/">Админ панель</a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="/logout.php">Выйти</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/login.php">Войти</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/register.php">Регистрация</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <?php
    $message = getMessage();
    if ($message):
    ?>
    <div class="alert alert-<?php echo $message['type'] === 'error' ? 'danger' : $message['type']; ?> alert-dismissible fade show" role="alert">
        <?php echo $message['message']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <main>
        <?php echo $content; ?>
    </main>

    <footer class="bg-dark text-light py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5><?php echo SITE_NAME; ?></h5>
                    <p>Концертное агентство, специализирующееся на организации культурно-массовых мероприятий.</p>
                </div>
                <div class="col-md-4">
                    <h5>Контакты</h5>
                    <p><i class="fas fa-phone me-2"></i> +7 (495) 123-45-67</p>
                    <p><i class="fas fa-envelope me-2"></i> info@kultproject.ru</p>
                    <p><i class="fas fa-map-marker-alt me-2"></i> Москва, ул. Тверская, 15</p>
                </div>
                <div class="col-md-4">
                    <h5>Следите за нами</h5>
                    <div class="social-links">
                        <a href="#" class="text-light me-3"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-light me-3"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-light me-3"><i class="fab fa-vk"></i></a>
                        <a href="#" class="text-light"><i class="fab fa-telegram"></i></a>
                    </div>
                </div>
            </div>
            <hr class="my-4">
            <div class="row">
                <div class="col-md-6">
                    <div class="text-center text-md-start">
                        <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. Все права защищены.</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="text-center text-md-end">
                        <a href="/terms.php" class="text-light me-3">Условия использования</a>
                        <a href="/privacy.php" class="text-light me-3">Политика конфиденциальности</a>
                        <a href="/contact.php" class="text-light">Контакты</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>
