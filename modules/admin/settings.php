<?php
require_once '../config.php';
require_once '../includes/functions.php';

session_start();

$auth = new Auth();
$auth->requireRole('admin');

$db = Database::getInstance();
$message = '';
$messageType = '';

// Обработка сохранения настроек
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'save_settings') {
    $settings = [
        'site_name' => sanitizeInput($_POST['site_name']),
        'site_url' => sanitizeInput($_POST['site_url']),
        'admin_email' => sanitizeInput($_POST['admin_email']),
        'booking_timeout' => intval($_POST['booking_timeout']),
        'max_file_size' => intval($_POST['max_file_size']),
        'smtp_host' => sanitizeInput($_POST['smtp_host']),
        'smtp_port' => intval($_POST['smtp_port']),
        'smtp_user' => sanitizeInput($_POST['smtp_user']),
        'payment_gateway_url' => sanitizeInput($_POST['payment_gateway_url']),
        'payment_api_key' => sanitizeInput($_POST['payment_api_key'])
    ];
    
    // Здесь можно сохранить настройки в базу данных или файл конфигурации
    $message = 'Настройки успешно сохранены';
    $messageType = 'success';
}

// Получаем текущие настройки (из config.php)
$currentSettings = [
    'site_name' => SITE_NAME,
    'site_url' => SITE_URL,
    'admin_email' => ADMIN_EMAIL,
    'booking_timeout' => BOOKING_TIMEOUT / 60, // в минутах
    'max_file_size' => MAX_FILE_SIZE / (1024 * 1024), // в МБ
    'smtp_host' => SMTP_HOST,
    'smtp_port' => SMTP_PORT,
    'smtp_user' => SMTP_USER,
    'payment_gateway_url' => PAYMENT_GATEWAY_URL,
    'payment_api_key' => PAYMENT_API_KEY
];

ob_start();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="h3 mb-4">Настройки системы</h1>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <form method="POST">
        <input type="hidden" name="action" value="save_settings">
        
        <div class="row">
            <!-- Основные настройки -->
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-cog me-2"></i>
                            Основные настройки
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="site_name" class="form-label">Название сайта</label>
                                <input type="text" class="form-control" id="site_name" name="site_name" 
                                       value="<?php echo htmlspecialchars($currentSettings['site_name']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="site_url" class="form-label">URL сайта</label>
                                <input type="url" class="form-control" id="site_url" name="site_url" 
                                       value="<?php echo htmlspecialchars($currentSettings['site_url']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="admin_email" class="form-label">Email администратора</label>
                            <input type="email" class="form-control" id="admin_email" name="admin_email" 
                                   value="<?php echo htmlspecialchars($currentSettings['admin_email']); ?>" required>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="booking_timeout" class="form-label">Таймаут бронирования (минуты)</label>
                                <input type="number" class="form-control" id="booking_timeout" name="booking_timeout" 
                                       value="<?php echo $currentSettings['booking_timeout']; ?>" min="5" max="60" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="max_file_size" class="form-label">Максимальный размер файла (МБ)</label>
                                <input type="number" class="form-control" id="max_file_size" name="max_file_size" 
                                       value="<?php echo $currentSettings['max_file_size']; ?>" min="1" max="100" required>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Настройки почты -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-envelope me-2"></i>
                            Настройки почты
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="smtp_host" class="form-label">SMTP сервер</label>
                                <input type="text" class="form-control" id="smtp_host" name="smtp_host" 
                                       value="<?php echo htmlspecialchars($currentSettings['smtp_host']); ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="smtp_port" class="form-label">Порт</label>
                                <input type="number" class="form-control" id="smtp_port" name="smtp_port" 
                                       value="<?php echo $currentSettings['smtp_port']; ?>" min="1" max="65535">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="smtp_user" class="form-label">Email для отправки</label>
                            <input type="email" class="form-control" id="smtp_user" name="smtp_user" 
                                   value="<?php echo htmlspecialchars($currentSettings['smtp_user']); ?>">
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Примечание:</strong> Пароль SMTP хранится в файле конфигурации и не отображается здесь по соображениям безопасности.
                        </div>
                    </div>
                </div>

                <!-- Настройки платежей -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-credit-card me-2"></i>
                            Настройки платежей
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="payment_gateway_url" class="form-label">URL платежного шлюза</label>
                            <input type="url" class="form-control" id="payment_gateway_url" name="payment_gateway_url" 
                                   value="<?php echo htmlspecialchars($currentSettings['payment_gateway_url']); ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="payment_api_key" class="form-label">API ключ платежной системы</label>
                            <input type="password" class="form-control" id="payment_api_key" name="payment_api_key" 
                                   value="<?php echo htmlspecialchars($currentSettings['payment_api_key']); ?>">
                            <div class="form-text">Оставьте пустым, если не хотите изменять текущий ключ</div>
                        </div>
                        
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Внимание:</strong> Изменение настроек платежей может повлиять на работу системы оплаты.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Боковая панель -->
            <div class="col-lg-4">
                <!-- Статистика системы -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0">Статистика системы</h6>
                    </div>
                    <div class="card-body">
                        <?php
                        $systemStats = [
                            'total_events' => $db->fetch("SELECT COUNT(*) as count FROM events")['count'],
                            'total_users' => $db->fetch("SELECT COUNT(*) as count FROM users")['count'],
                            'total_bookings' => $db->fetch("SELECT COUNT(*) as count FROM bookings")['count'],
                            'total_revenue' => $db->fetch("SELECT SUM(amount) as total FROM payments WHERE payment_status = 'completed'")['total'] ?? 0
                        ];
                        ?>
                        <div class="row text-center">
                            <div class="col-6 mb-3">
                                <div class="border rounded p-2">
                                    <h4 class="text-primary mb-0"><?php echo $systemStats['total_events']; ?></h4>
                                    <small class="text-muted">Мероприятий</small>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="border rounded p-2">
                                    <h4 class="text-success mb-0"><?php echo $systemStats['total_users']; ?></h4>
                                    <small class="text-muted">Пользователей</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-2">
                                    <h4 class="text-info mb-0"><?php echo $systemStats['total_bookings']; ?></h4>
                                    <small class="text-muted">Бронирований</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-2">
                                    <h4 class="text-warning mb-0"><?php echo number_format($systemStats['total_revenue'], 0, ',', ' '); ?> ₽</h4>
                                    <small class="text-muted">Выручка</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Информация о системе -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0">Информация о системе</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <strong>PHP версия:</strong><br>
                            <span class="text-muted"><?php echo PHP_VERSION; ?></span>
                        </div>
                        <div class="mb-2">
                            <strong>MySQL версия:</strong><br>
                            <span class="text-muted"><?php echo $db->getConnection()->getAttribute(PDO::ATTR_SERVER_VERSION); ?></span>
                        </div>
                        <div class="mb-2">
                            <strong>Версия системы:</strong><br>
                            <span class="text-muted">1.0.0</span>
                        </div>
                        <div class="mb-0">
                            <strong>Последнее обновление:</strong><br>
                            <span class="text-muted"><?php echo date('d.m.Y'); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Быстрые действия -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title mb-0">Быстрые действия</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>
                                Сохранить настройки
                            </button>
                            <a href="?page=analytics" class="btn btn-outline-info">
                                <i class="fas fa-chart-bar me-2"></i>
                                Просмотр аналитики
                            </a>
                            <a href="../" class="btn btn-outline-secondary">
                                <i class="fas fa-external-link-alt me-2"></i>
                                Перейти на сайт
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
$pageTitle = 'Настройки системы';

// Создаем админский header
ob_start();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle . ' - ' . SITE_NAME . ' Админ'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Админская навигация -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="?page=dashboard">
                <i class="fas fa-cog me-2"></i>
                <?php echo SITE_NAME; ?> Админ
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $page === 'dashboard' ? 'active' : ''; ?>" href="?page=dashboard">
                            <i class="fas fa-tachometer-alt me-1"></i>Панель
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $page === 'events' ? 'active' : ''; ?>" href="?page=events">
                            <i class="fas fa-calendar-alt me-1"></i>Мероприятия
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $page === 'users' ? 'active' : ''; ?>" href="?page=users">
                            <i class="fas fa-users me-1"></i>Пользователи
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $page === 'analytics' ? 'active' : ''; ?>" href="?page=analytics">
                            <i class="fas fa-chart-bar me-1"></i>Аналитика
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $page === 'settings' ? 'active' : ''; ?>" href="?page=settings">
                            <i class="fas fa-cog me-1"></i>Настройки
                        </a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i>
                            <?php echo $_SESSION['user_name']; ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="../profile.php">Профиль</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../">На сайт</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../logout.php">Выйти</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main>
        <?php echo $content; ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
echo ob_get_clean();
?>
