<?php
// admin/dashboard.php - Панель управления с общей статистикой
$db = Database::getInstance();
$eventManager = new EventManager();

// Получаем статистику
$stats = [
    'total_events' => $db->fetch("SELECT COUNT(*) as count FROM events")['count'],
    'published_events' => $db->fetch("SELECT COUNT(*) as count FROM events WHERE status = 'published'")['count'],
    'draft_events' => $db->fetch("SELECT COUNT(*) as count FROM events WHERE status = 'draft'")['count'],
    'total_users' => $db->fetch("SELECT COUNT(*) as count FROM users WHERE role = 'client'")['count'],
    'total_admins' => $db->fetch("SELECT COUNT(*) as count FROM users WHERE role IN ('admin', 'manager')")['count'],
    'total_bookings' => $db->fetch("SELECT COUNT(*) as count FROM bookings WHERE status = 'confirmed'")['count'],
    'pending_bookings' => $db->fetch("SELECT COUNT(*) as count FROM bookings WHERE status = 'pending'")['count'],
    'total_revenue' => $db->fetch("SELECT SUM(amount) as total FROM payments WHERE payment_status = 'completed'")['total'] ?? 0,
    'monthly_revenue' => $db->fetch("SELECT SUM(amount) as total FROM payments WHERE payment_status = 'completed' AND MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())")['total'] ?? 0,
];

// Получаем последние мероприятия
$recentEvents = $db->fetchAll("SELECT * FROM events ORDER BY created_at DESC LIMIT 5");

// Получаем последних пользователей
$recentUsers = $db->fetchAll("SELECT * FROM users WHERE role = 'client' ORDER BY created_at DESC LIMIT 5");

// Получаем последние бронирования
$recentBookings = $db->fetchAll("
    SELECT b.*, u.first_name, u.last_name, e.title as event_title 
    FROM bookings b 
    JOIN users u ON b.user_id = u.id 
    JOIN events e ON b.event_id = e.id 
    ORDER BY b.created_at DESC 
    LIMIT 5
");

ob_start();
?>

<div class="row">
    <div class="col-12">
        <h1 class="h3 mb-4 text-gray-800">Панель управления</h1>
        <p class="text-muted mb-4">Добро пожаловать в админ-панель <?php echo defined('SITE_NAME') ? SITE_NAME : 'КультПросвет'; ?>!</p>
    </div>
</div>

<!-- Статистические карточки -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Всего мероприятий
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total_events']; ?></div>
                    </div>
                    <div class="col-auto">
                        <div class="stats-icon bg-primary">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Опубликованные
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['published_events']; ?></div>
                    </div>
                    <div class="col-auto">
                        <div class="stats-icon bg-success">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Пользователи
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total_users']; ?></div>
                    </div>
                    <div class="col-auto">
                        <div class="stats-icon bg-info">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Выручка (месяц)
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo formatPrice($stats['monthly_revenue']); ?></div>
                    </div>
                    <div class="col-auto">
                        <div class="stats-icon bg-warning">
                            <i class="fas fa-ruble-sign"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Дополнительная статистика -->
<div class="row mb-4">
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-chart-pie me-2"></i>Статистика мероприятий
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <div class="text-center">
                            <div class="h4 text-primary"><?php echo $stats['published_events']; ?></div>
                            <div class="text-muted">Опубликованные</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center">
                            <div class="h4 text-warning"><?php echo $stats['draft_events']; ?></div>
                            <div class="text-muted">Черновики</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-success">
                    <i class="fas fa-ticket-alt me-2"></i>Статистика бронирований
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <div class="text-center">
                            <div class="h4 text-success"><?php echo $stats['total_bookings']; ?></div>
                            <div class="text-muted">Подтвержденные</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center">
                            <div class="h4 text-warning"><?php echo $stats['pending_bookings']; ?></div>
                            <div class="text-muted">Ожидающие</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Последние данные -->
<div class="row">
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-calendar-alt me-2"></i>Последние мероприятия
                </h6>
            </div>
            <div class="card-body">
                <?php if (empty($recentEvents)): ?>
                    <p class="text-muted">Мероприятий пока нет</p>
                <?php else: ?>
                    <?php foreach ($recentEvents as $event): ?>
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="fas fa-calendar"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0"><?php echo htmlspecialchars($event['title']); ?></h6>
                                <small class="text-muted">
                                    <?php echo formatDate($event['event_date']); ?> | 
                                    <span class="badge bg-<?php echo $event['status'] === 'published' ? 'success' : 'warning'; ?>">
                                        <?php echo $event['status'] === 'published' ? 'Опубликовано' : 'Черновик'; ?>
                                    </span>
                                </small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div class="text-center">
                        <a href="?page=events" class="btn btn-sm btn-outline-primary">Все мероприятия</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-success">
                    <i class="fas fa-users me-2"></i>Последние пользователи
                </h6>
            </div>
            <div class="card-body">
                <?php if (empty($recentUsers)): ?>
                    <p class="text-muted">Пользователей пока нет</p>
                <?php else: ?>
                    <?php foreach ($recentUsers as $user): ?>
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0">
                                <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="fas fa-user"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h6>
                                <small class="text-muted"><?php echo htmlspecialchars($user['email']); ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div class="text-center">
                        <a href="?page=users" class="btn btn-sm btn-outline-success">Все пользователи</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-info">
                    <i class="fas fa-ticket-alt me-2"></i>Последние бронирования
                </h6>
            </div>
            <div class="card-body">
                <?php if (empty($recentBookings)): ?>
                    <p class="text-muted">Бронирований пока нет</p>
                <?php else: ?>
                    <?php foreach ($recentBookings as $booking): ?>
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0">
                                <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="fas fa-ticket-alt"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0"><?php echo htmlspecialchars($booking['event_title']); ?></h6>
                                <small class="text-muted">
                                    <?php echo htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']); ?> | 
                                    <span class="badge bg-<?php echo $booking['status'] === 'confirmed' ? 'success' : 'warning'; ?>">
                                        <?php echo $booking['status'] === 'confirmed' ? 'Подтверждено' : 'Ожидает'; ?>
                                    </span>
                                </small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div class="text-center">
                        <a href="?page=analytics" class="btn btn-sm btn-outline-info">Все бронирования</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Быстрые действия -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-bolt me-2"></i>Быстрые действия
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <a href="?page=events&action=create" class="btn btn-primary w-100">
                            <i class="fas fa-plus me-2"></i>Создать мероприятие
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="?page=users" class="btn btn-success w-100">
                            <i class="fas fa-users me-2"></i>Управление пользователями
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="?page=analytics" class="btn btn-info w-100">
                            <i class="fas fa-chart-bar me-2"></i>Аналитика
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="?page=settings" class="btn btn-warning w-100">
                            <i class="fas fa-cog me-2"></i>Настройки
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = 'Панель управления';
include 'admin-header.php';
?>
