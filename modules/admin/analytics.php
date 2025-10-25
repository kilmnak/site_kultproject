<?php
require_once '../config.php';
require_once '../includes/functions.php';

session_start();

$auth = new Auth();
$auth->requireRole('admin');

$db = Database::getInstance();
$analyticsManager = new AnalyticsManager();

// Параметры фильтрации
$startDate = $_GET['start_date'] ?? date('Y-m-01'); // Первый день текущего месяца
$endDate = $_GET['end_date'] ?? date('Y-m-d'); // Сегодня
$eventId = intval($_GET['event_id'] ?? 0);

// Получаем общую статистику
$overallStats = [
    'total_events' => $db->fetch("SELECT COUNT(*) as count FROM events")['count'],
    'published_events' => $db->fetch("SELECT COUNT(*) as count FROM events WHERE status = 'published'")['count'],
    'total_users' => $db->fetch("SELECT COUNT(*) as count FROM users WHERE role = 'client'")['count'],
    'total_bookings' => $db->fetch("SELECT COUNT(*) as count FROM bookings WHERE status = 'confirmed'")['count'],
    'total_revenue' => $db->fetch("SELECT SUM(amount) as total FROM payments WHERE payment_status = 'completed'")['total'] ?? 0,
    'avg_ticket_price' => $db->fetch("SELECT AVG(price) as avg FROM ticket_orders")['avg'] ?? 0,
];

// Получаем статистику за период
$periodStats = $db->fetch(
    "SELECT 
        COUNT(DISTINCT b.id) as bookings_count,
        COUNT(DISTINCT t.id) as tickets_sold,
        SUM(p.amount) as revenue,
        AVG(p.amount) as avg_booking_value
     FROM bookings b
     LEFT JOIN ticket_orders t ON b.id = t.booking_id
     LEFT JOIN payments p ON b.id = p.booking_id AND p.payment_status = 'completed'
     WHERE b.created_at BETWEEN ? AND ? AND b.status = 'confirmed'",
    [$startDate . ' 00:00:00', $endDate . ' 23:59:59']
);

// Получаем отчет по продажам
$salesReport = $analyticsManager->getSalesReport($startDate . ' 00:00:00', $endDate . ' 23:59:59');

// Получаем топ мероприятий
$topEvents = $analyticsManager->getTopEvents(10);

// Получаем статистику по мероприятиям
$eventsStats = $db->fetchAll(
    "SELECT 
        e.id,
        e.title,
        e.event_date,
        e.status,
        COUNT(DISTINCT b.id) as bookings_count,
        COUNT(DISTINCT t.id) as tickets_sold,
        SUM(p.amount) as revenue,
        AVG(r.rating) as avg_rating
     FROM events e
     LEFT JOIN bookings b ON e.id = b.event_id AND b.status = 'confirmed'
     LEFT JOIN ticket_orders t ON b.id = t.booking_id
     LEFT JOIN payments p ON b.id = p.booking_id AND p.payment_status = 'completed'
     LEFT JOIN reviews r ON e.id = r.event_id
     GROUP BY e.id, e.title, e.event_date, e.status
     ORDER BY revenue DESC"
);

ob_start();
?>

<div class="container-fluid my-4">
    <div class="row">
        <div class="col-12">
            <h1 class="h3 mb-4">Аналитика и отчеты</h1>
        </div>
    </div>
    
    <!-- Фильтры -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Фильтры</h6>
                </div>
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label for="start_date" class="form-label">Дата начала</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $startDate; ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="end_date" class="form-label">Дата окончания</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $endDate; ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="event_id" class="form-label">Мероприятие</label>
                            <select class="form-select" id="event_id" name="event_id">
                                <option value="0">Все мероприятия</option>
                                <?php foreach ($eventsStats as $event): ?>
                                    <option value="<?php echo $event['id']; ?>" <?php echo $eventId === $event['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($event['title']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter me-2"></i>
                                Применить фильтры
                            </button>
                            <a href="analytics.php" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>
                                Сбросить
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Общая статистика -->
    <div class="row mb-4">
        <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Всего мероприятий
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo $overallStats['total_events']; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Пользователи
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo $overallStats['total_users']; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Бронирования
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo $overallStats['total_bookings']; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-ticket-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Выручка
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo formatPrice($overallStats['total_revenue']); ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-ruble-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Ср. цена билета
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo formatPrice($overallStats['avg_ticket_price']); ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tag fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
            <div class="card border-left-secondary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">
                                За период
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo $periodStats['bookings_count'] ?? 0; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Топ мероприятий -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Топ мероприятий по выручке</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($topEvents)): ?>
                        <p class="text-muted">Нет данных</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Мероприятие</th>
                                        <th>Билетов</th>
                                        <th>Выручка</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($topEvents as $event): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($event['title']); ?></td>
                                            <td><?php echo $event['tickets_sold']; ?></td>
                                            <td><?php echo formatPrice($event['revenue']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Статистика по мероприятиям -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Статистика по мероприятиям</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($eventsStats)): ?>
                        <p class="text-muted">Нет данных</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Мероприятие</th>
                                        <th>Дата</th>
                                        <th>Статус</th>
                                        <th>Билетов</th>
                                        <th>Выручка</th>
                                        <th>Рейтинг</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_slice($eventsStats, 0, 10) as $event): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars(substr($event['title'], 0, 30)) . '...'; ?></td>
                                            <td><?php echo formatDate($event['event_date'], 'd.m.Y'); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $event['status'] === 'published' ? 'success' : 'warning'; ?>">
                                                    <?php echo $event['status'] === 'published' ? 'Опубликовано' : 'Черновик'; ?>
                                                </span>
                                            </td>
                                            <td><?php echo $event['tickets_sold']; ?></td>
                                            <td><?php echo formatPrice($event['revenue']); ?></td>
                                            <td>
                                                <?php if ($event['avg_rating']): ?>
                                                    <span class="badge bg-info"><?php echo number_format($event['avg_rating'], 1); ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Отчет по продажам -->
    <?php if (!empty($salesReport)): ?>
        <div class="row">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-primary">Отчет по продажам за период</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Дата</th>
                                        <th>Количество бронирований</th>
                                        <th>Выручка за день</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($salesReport as $day): ?>
                                        <tr>
                                            <td><?php echo formatDate($day['date'], 'd.m.Y'); ?></td>
                                            <td><?php echo $day['bookings_count']; ?></td>
                                            <td><?php echo formatPrice($day['daily_revenue']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr class="table-primary">
                                        <th>Итого:</th>
                                        <th><?php echo array_sum(array_column($salesReport, 'bookings_count')); ?></th>
                                        <th><?php echo formatPrice(array_sum(array_column($salesReport, 'daily_revenue'))); ?></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
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
.border-left-danger {
    border-left: 0.25rem solid #e74a3b !important;
}
.border-left-secondary {
    border-left: 0.25rem solid #858796 !important;
}
</style>

<?php
$content = ob_get_clean();
$pageTitle = 'Аналитика и отчеты';

include '../header.php';
?>
