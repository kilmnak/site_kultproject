<?php
require_once 'config.php';
require_once 'includes/functions.php';

session_start();

$auth = new Auth();
$auth->requireLogin();

$db = Database::getInstance();
$user = $auth->getCurrentUser();

// Получаем бронирования пользователя
$bookings = $db->fetchAll(
    "SELECT 
        b.*,
        e.title as event_title,
        e.event_date,
        e.venue,
        COUNT(to.id) as tickets_count
     FROM bookings b
     JOIN events e ON b.event_id = e.id
     LEFT JOIN ticket_orders to ON b.id = to.booking_id
     WHERE b.user_id = ?
     GROUP BY b.id
     ORDER BY b.created_at DESC",
    [$user['id']]
);

ob_start();
?>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-3">
            <!-- Боковое меню -->
            <div class="card">
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="avatar bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                            <i class="fas fa-user fa-2x"></i>
                        </div>
                        <h5 class="mt-2 mb-0"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h5>
                        <small class="text-muted"><?php echo htmlspecialchars($user['email']); ?></small>
                    </div>
                    
                    <nav class="nav flex-column">
                        <a class="nav-link" href="/profile.php">
                            <i class="fas fa-user me-2"></i>
                            Профиль
                        </a>
                        <a class="nav-link" href="/my-tickets.php">
                            <i class="fas fa-ticket-alt me-2"></i>
                            Мои билеты
                        </a>
                        <a class="nav-link active" href="/my-bookings.php">
                            <i class="fas fa-calendar-check me-2"></i>
                            Мои бронирования
                        </a>
                        <a class="nav-link" href="/notifications.php">
                            <i class="fas fa-bell me-2"></i>
                            Уведомления
                        </a>
                    </nav>
                </div>
            </div>
        </div>
        
        <div class="col-lg-9">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Мои бронирования</h5>
                    <span class="badge bg-primary"><?php echo count($bookings); ?> бронирований</span>
                </div>
                <div class="card-body">
                    <?php if (empty($bookings)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-calendar-check text-muted display-1 mb-3"></i>
                            <h5 class="text-muted">У вас пока нет бронирований</h5>
                            <p class="text-muted">Забронируйте места на интересные мероприятия!</p>
                            <a href="/events.php" class="btn btn-primary">
                                <i class="fas fa-calendar me-2"></i>
                                Посмотреть мероприятия
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Мероприятие</th>
                                        <th>Дата</th>
                                        <th>Место</th>
                                        <th>Билетов</th>
                                        <th>Сумма</th>
                                        <th>Статус</th>
                                        <th>Действия</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($bookings as $booking): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($booking['event_title']); ?></strong>
                                                <br><small class="text-muted"><?php echo htmlspecialchars($booking['booking_code']); ?></small>
                                            </td>
                                            <td><?php echo formatDate($booking['event_date'], 'd.m.Y H:i'); ?></td>
                                            <td><?php echo htmlspecialchars($booking['venue']); ?></td>
                                            <td><?php echo $booking['tickets_count']; ?></td>
                                            <td><?php echo formatPrice($booking['total_amount']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    echo $booking['status'] === 'confirmed' ? 'success' : 
                                                        ($booking['status'] === 'pending' ? 'warning' : 'secondary'); 
                                                ?>">
                                                    <?php 
                                                    $statusLabels = [
                                                        'pending' => 'Ожидает оплаты',
                                                        'confirmed' => 'Подтверждено',
                                                        'cancelled' => 'Отменено',
                                                        'expired' => 'Истекло'
                                                    ];
                                                    echo $statusLabels[$booking['status']] ?? $booking['status'];
                                                    ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <?php if ($booking['status'] === 'pending'): ?>
                                                        <a href="/booking.php?id=<?php echo $booking['id']; ?>" class="btn btn-primary">
                                                            <i class="fas fa-credit-card"></i>
                                                        </a>
                                                    <?php elseif ($booking['status'] === 'confirmed'): ?>
                                                        <a href="/my-tickets.php" class="btn btn-success">
                                                            <i class="fas fa-ticket-alt"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    <button class="btn btn-outline-info" onclick="showBookingDetails(<?php echo $booking['id']; ?>)">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </div>
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
</div>

<!-- Модальное окно для деталей бронирования -->
<div class="modal fade" id="bookingModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Детали бронирования</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="bookingDetails">
                <!-- Содержимое загружается через AJAX -->
            </div>
        </div>
    </div>
</div>

<script>
function showBookingDetails(bookingId) {
    // Здесь должен быть AJAX-запрос для получения деталей бронирования
    document.getElementById('bookingDetails').innerHTML = '<div class="text-center"><div class="spinner-border" role="status"></div></div>';
    new bootstrap.Modal(document.getElementById('bookingModal')).show();
}
</script>

<?php
$content = ob_get_clean();
$pageTitle = 'Мои бронирования';

include 'header.php';
?>
