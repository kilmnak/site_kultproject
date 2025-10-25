<?php
// admin/analytics.php - Аналитика и отчеты
$db = Database::getInstance();

// Параметры фильтрации
$startDate = $_GET['start_date'] ?? date('Y-m-01'); // Первый день текущего месяца
$endDate = $_GET['end_date'] ?? date('Y-m-d'); // Сегодня
$eventId = intval($_GET['event_id'] ?? 0);

// Получаем аналитику
$analytics = [
    'total_revenue' => $db->fetch("SELECT SUM(amount) as total FROM payments WHERE payment_status = 'completed'")['total'] ?? 0,
    'monthly_revenue' => $db->fetch("SELECT SUM(amount) as total FROM payments WHERE payment_status = 'completed' AND MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())")['total'] ?? 0,
    'period_revenue' => $db->fetch("SELECT SUM(amount) as total FROM payments WHERE payment_status = 'completed' AND DATE(created_at) BETWEEN ? AND ?", [$startDate, $endDate])['total'] ?? 0,
    'total_bookings' => $db->fetch("SELECT COUNT(*) as count FROM bookings WHERE status = 'confirmed'")['count'],
    'period_bookings' => $db->fetch("SELECT COUNT(*) as count FROM bookings WHERE status = 'confirmed' AND DATE(created_at) BETWEEN ? AND ?", [$startDate, $endDate])['count'],
    'total_events' => $db->fetch("SELECT COUNT(*) as count FROM events")['count'],
    'published_events' => $db->fetch("SELECT COUNT(*) as count FROM events WHERE status = 'published'")['count'],
    'total_users' => $db->fetch("SELECT COUNT(*) as count FROM users WHERE role = 'client'")['count'],
];

// Получаем топ мероприятий
$topEvents = $db->fetchAll("
    SELECT e.*, 
           COUNT(b.id) as bookings_count,
           SUM(p.amount) as revenue
    FROM events e
    LEFT JOIN bookings b ON e.id = b.event_id AND b.status = 'confirmed'
    LEFT JOIN payments p ON b.id = p.booking_id AND p.payment_status = 'completed'
    GROUP BY e.id
    ORDER BY revenue DESC
    LIMIT 10
");

// Получаем статистику по месяцам
$monthlyStats = $db->fetchAll("
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        COUNT(*) as bookings_count,
        SUM(amount) as revenue
    FROM payments 
    WHERE payment_status = 'completed'
    AND created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month ASC
");

// Получаем статистику по дням недели
$weeklyStats = $db->fetchAll("
    SELECT 
        DAYNAME(created_at) as day_name,
        DAYOFWEEK(created_at) as day_number,
        COUNT(*) as bookings_count,
        AVG(amount) as avg_amount
    FROM payments 
    WHERE payment_status = 'completed'
    AND created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 3 MONTH)
    GROUP BY DAYOFWEEK(created_at), DAYNAME(created_at)
    ORDER BY day_number
");

// Получаем список мероприятий для фильтра
$events = $db->fetchAll("SELECT id, title FROM events WHERE status = 'published' ORDER BY title");

ob_start();
?>

<div class="row">
    <div class="col-12">
        <h1 class="h3 mb-4 text-gray-800">Аналитика и отчеты</h1>
    </div>
</div>

<!-- Фильтры -->
<div class="card mb-4">
    <div class="card-header">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-filter me-2"></i>Фильтры
        </h6>
    </div>
    <div class="card-body">
        <form method="GET" class="row align-items-end">
            <input type="hidden" name="page" value="analytics">
            
            <div class="col-md-3">
                <label for="start_date" class="form-label">Дата начала</label>
                <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $startDate; ?>">
            </div>
            
            <div class="col-md-3">
                <label for="end_date" class="form-label">Дата окончания</label>
                <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $endDate; ?>">
            </div>
            
            <div class="col-md-3">
                <label for="event_id" class="form-label">Мероприятие</label>
                <select class="form-select" id="event_id" name="event_id">
                    <option value="0">Все мероприятия</option>
                    <?php foreach ($events as $event): ?>
                        <option value="<?php echo $event['id']; ?>" <?php echo $eventId === $event['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($event['title']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search me-2"></i>Применить фильтр
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Основная статистика -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Общая выручка
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo formatPrice($analytics['total_revenue']); ?></div>
                    </div>
                    <div class="col-auto">
                        <div class="stats-icon bg-success">
                            <i class="fas fa-ruble-sign"></i>
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
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Выручка за период
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo formatPrice($analytics['period_revenue']); ?></div>
                    </div>
                    <div class="col-auto">
                        <div class="stats-icon bg-primary">
                            <i class="fas fa-chart-line"></i>
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
                            Бронирования за период
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $analytics['period_bookings']; ?></div>
                    </div>
                    <div class="col-auto">
                        <div class="stats-icon bg-info">
                            <i class="fas fa-ticket-alt"></i>
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
                            Средний чек
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo $analytics['period_bookings'] > 0 ? formatPrice($analytics['period_revenue'] / $analytics['period_bookings']) : '0 ₽'; ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <div class="stats-icon bg-warning">
                            <i class="fas fa-calculator"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Графики и диаграммы -->
<div class="row mb-4">
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-chart-line me-2"></i>Динамика выручки по месяцам
                </h6>
            </div>
            <div class="card-body">
                <canvas id="revenueChart" height="100"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-success">
                    <i class="fas fa-chart-pie me-2"></i>Статистика по дням недели
                </h6>
            </div>
            <div class="card-body">
                <canvas id="weeklyChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Топ мероприятий -->
<div class="row">
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-trophy me-2"></i>Топ мероприятий по выручке
                </h6>
            </div>
            <div class="card-body">
                <?php if (empty($topEvents)): ?>
                    <p class="text-muted text-center py-4">Данных для анализа пока нет</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Мероприятие</th>
                                    <th>Дата</th>
                                    <th>Бронирования</th>
                                    <th>Выручка</th>
                                    <th>Средний чек</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($topEvents as $event): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($event['title']); ?></strong>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($event['venue']); ?></small>
                                        </td>
                                        <td><?php echo formatDate($event['event_date'], 'd.m.Y'); ?></td>
                                        <td>
                                            <span class="badge bg-info"><?php echo $event['bookings_count']; ?></span>
                                        </td>
                                        <td>
                                            <strong class="text-success"><?php echo formatPrice($event['revenue'] ?? 0); ?></strong>
                                        </td>
                                        <td>
                                            <?php 
                                            $avgTicket = $event['bookings_count'] > 0 ? ($event['revenue'] ?? 0) / $event['bookings_count'] : 0;
                                            echo formatPrice($avgTicket);
                                            ?>
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

    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-info">
                    <i class="fas fa-info-circle me-2"></i>Общая статистика
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Всего мероприятий:</span>
                        <strong><?php echo $analytics['total_events']; ?></strong>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Опубликованных:</span>
                        <strong><?php echo $analytics['published_events']; ?></strong>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Всего пользователей:</span>
                        <strong><?php echo $analytics['total_users']; ?></strong>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Всего бронирований:</span>
                        <strong><?php echo $analytics['total_bookings']; ?></strong>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Выручка за месяц:</span>
                        <strong class="text-success"><?php echo formatPrice($analytics['monthly_revenue']); ?></strong>
                    </div>
                </div>
                
                <hr>
                
                <div class="text-center">
                    <small class="text-muted">
                        Период: <?php echo formatDate($startDate, 'd.m.Y'); ?> - <?php echo formatDate($endDate, 'd.m.Y'); ?>
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Экспорт отчетов -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-download me-2"></i>Экспорт отчетов
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <button class="btn btn-outline-primary w-100" onclick="exportReport('revenue')">
                            <i class="fas fa-file-excel me-2"></i>Отчет по выручке
                        </button>
                    </div>
                    <div class="col-md-3 mb-3">
                        <button class="btn btn-outline-success w-100" onclick="exportReport('bookings')">
                            <i class="fas fa-file-csv me-2"></i>Отчет по бронированиям
                        </button>
                    </div>
                    <div class="col-md-3 mb-3">
                        <button class="btn btn-outline-info w-100" onclick="exportReport('events')">
                            <i class="fas fa-file-pdf me-2"></i>Отчет по мероприятиям
                        </button>
                    </div>
                    <div class="col-md-3 mb-3">
                        <button class="btn btn-outline-warning w-100" onclick="exportReport('users')">
                            <i class="fas fa-file-alt me-2"></i>Отчет по пользователям
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// График выручки по месяцам
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
const revenueChart = new Chart(revenueCtx, {
    type: 'line',
    data: {
        labels: [<?php echo implode(',', array_map(function($stat) { return "'" . date('M Y', strtotime($stat['month'] . '-01')) . "'"; }, $monthlyStats)); ?>],
        datasets: [{
            label: 'Выручка (₽)',
            data: [<?php echo implode(',', array_column($monthlyStats, 'revenue')); ?>],
            borderColor: 'rgb(78, 115, 223)',
            backgroundColor: 'rgba(78, 115, 223, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return new Intl.NumberFormat('ru-RU').format(value) + ' ₽';
                    }
                }
            }
        }
    }
});

// График по дням недели
const weeklyCtx = document.getElementById('weeklyChart').getContext('2d');
const weeklyChart = new Chart(weeklyCtx, {
    type: 'doughnut',
    data: {
        labels: [<?php echo implode(',', array_map(function($stat) { return "'" . $stat['day_name'] . "'"; }, $weeklyStats)); ?>],
        datasets: [{
            data: [<?php echo implode(',', array_column($weeklyStats, 'bookings_count')); ?>],
            backgroundColor: [
                '#4e73df',
                '#1cc88a',
                '#36b9cc',
                '#f6c23e',
                '#e74a3b',
                '#858796',
                '#5a5c69'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Экспорт отчетов
function exportReport(type) {
    const params = new URLSearchParams({
        page: 'analytics',
        export: type,
        start_date: '<?php echo $startDate; ?>',
        end_date: '<?php echo $endDate; ?>',
        event_id: '<?php echo $eventId; ?>'
    });
    
    window.open('?' + params.toString(), '_blank');
}
</script>

<?php
$content = ob_get_clean();
$pageTitle = 'Аналитика и отчеты';
include 'admin-header.php';
?>
