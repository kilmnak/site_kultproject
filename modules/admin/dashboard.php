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

<div class="container-fluid my-4">
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

include '../header.php';
?>
