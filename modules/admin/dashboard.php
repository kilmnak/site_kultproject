<?php
require_once '../config.php';
require_once '../includes/functions.php';

session_start();

$auth = new Auth();
$auth->requireRole('admin');

$db = Database::getInstance();
$eventManager = new EventManager();
$analyticsManager = new AnalyticsManager();

// Получаем статистику
$stats = [
    'total_events' => $db->fetch("SELECT COUNT(*) as count FROM events")['count'],
    'published_events' => $db->fetch("SELECT COUNT(*) as count FROM events WHERE status = 'published'")['count'],
    'total_users' => $db->fetch("SELECT COUNT(*) as count FROM users WHERE role = 'client'")['count'],
    'total_bookings' => $db->fetch("SELECT COUNT(*) as count FROM bookings WHERE status = 'confirmed'")['count'],
    'total_revenue' => $db->fetch("SELECT SUM(amount) as total FROM payments WHERE payment_status = 'completed'")['total'] ?? 0,
];

// Получаем последние мероприятия
$recentEvents = $eventManager->getEvents('published', 5);

// Получаем топ мероприятий
$topEvents = $analyticsManager->getTopEvents(5);

ob_start();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="h3 mb-4">Панель администратора</h1>
        </div>
    </div>
    
    <!-- Статистика -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Всего мероприятий
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo $stats['total_events']; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Пользователи
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo $stats['total_users']; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Бронирования
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo $stats['total_bookings']; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-ticket-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Выручка
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo formatPrice($stats['total_revenue']); ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-ruble-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Последние мероприятия -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Последние мероприятия</h6>
                    <a href="events.php" class="btn btn-sm btn-primary">Все мероприятия</a>
                </div>
                <div class="card-body">
                    <?php if (empty($recentEvents)): ?>
                        <p class="text-muted">Нет мероприятий</p>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($recentEvents as $event): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1"><?php echo htmlspecialchars($event['title']); ?></h6>
                                        <small class="text-muted">
                                            <?php echo formatDate($event['event_date'], 'd.m.Y H:i'); ?> • 
                                            <?php echo htmlspecialchars($event['venue']); ?>
                                        </small>
                                    </div>
                                    <span class="badge bg-<?php echo $event['status'] === 'published' ? 'success' : 'warning'; ?>">
                                        <?php echo $event['status'] === 'published' ? 'Опубликовано' : 'Черновик'; ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Топ мероприятий -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Топ мероприятий</h6>
                    <a href="analytics.php" class="btn btn-sm btn-primary">Подробная аналитика</a>
                </div>
                <div class="card-body">
                    <?php if (empty($topEvents)): ?>
                        <p class="text-muted">Нет данных</p>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($topEvents as $event): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1"><?php echo htmlspecialchars($event['title']); ?></h6>
                                        <small class="text-muted">
                                            <?php echo $event['tickets_sold']; ?> билетов продано
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        <div class="h6 text-primary mb-0"><?php echo formatPrice($event['revenue']); ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Быстрые действия -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Быстрые действия</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="events.php?action=create" class="btn btn-primary w-100">
                                <i class="fas fa-plus me-2"></i>
                                Создать мероприятие
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="users.php" class="btn btn-info w-100">
                                <i class="fas fa-users me-2"></i>
                                Управление пользователями
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="bookings.php" class="btn btn-success w-100">
                                <i class="fas fa-ticket-alt me-2"></i>
                                Бронирования
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="analytics.php" class="btn btn-warning w-100">
                                <i class="fas fa-chart-bar me-2"></i>
                                Аналитика
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}
.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}
.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}
.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}
</style>

<?php
$content = ob_get_clean();
$pageTitle = 'Панель администратора';

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
                        <a class="nav-link active" href="?page=dashboard">
                            <i class="fas fa-tachometer-alt me-1"></i>Панель
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="?page=events">
                            <i class="fas fa-calendar-alt me-1"></i>Мероприятия
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="?page=users">
                            <i class="fas fa-users me-1"></i>Пользователи
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="?page=analytics">
                            <i class="fas fa-chart-bar me-1"></i>Аналитика
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="?page=settings">
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
