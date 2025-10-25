<?php
require_once 'config.php';
require_once 'includes/functions.php';

session_start();

$auth = new Auth();
$auth->requireLogin();

$db = Database::getInstance();
$user = $auth->getCurrentUser();

// Получаем билеты пользователя
$tickets = $db->fetchAll(
    "SELECT 
        t.*,
        e.title as event_title,
        e.event_date,
        e.venue,
        s.seat_number,
        s.row_number,
        s.section,
        pc.name as category_name,
        pc.price
     FROM tickets t
     JOIN ticket_orders `to` ON t.order_id = `to`.id
     JOIN bookings b ON `to`.booking_id = b.id
     JOIN events e ON b.event_id = e.id
     JOIN seats s ON `to`.seat_id = s.id
     LEFT JOIN price_categories pc ON s.price_category_id = pc.id
     WHERE b.user_id = ?
     ORDER BY e.event_date DESC, t.created_at DESC",
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
                        <a class="nav-link active" href="/my-tickets.php">
                            <i class="fas fa-ticket-alt me-2"></i>
                            Мои билеты
                        </a>
                        <a class="nav-link" href="/my-bookings.php">
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
                    <h5 class="mb-0">Мои билеты</h5>
                    <span class="badge bg-primary"><?php echo count($tickets); ?> билетов</span>
                </div>
                <div class="card-body">
                    <?php if (empty($tickets)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-ticket-alt text-muted display-1 mb-3"></i>
                            <h5 class="text-muted">У вас пока нет билетов</h5>
                            <p class="text-muted">Купите билеты на интересные мероприятия!</p>
                            <a href="/events.php" class="btn btn-primary">
                                <i class="fas fa-calendar me-2"></i>
                                Посмотреть мероприятия
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($tickets as $ticket): ?>
                                <div class="col-md-6 mb-4">
                                    <div class="card h-100 border-<?php echo $ticket['status'] === 'active' ? 'success' : ($ticket['status'] === 'used' ? 'info' : 'secondary'); ?>">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <h6 class="card-title mb-0"><?php echo htmlspecialchars($ticket['event_title']); ?></h6>
                                                <span class="badge bg-<?php echo $ticket['status'] === 'active' ? 'success' : ($ticket['status'] === 'used' ? 'info' : 'secondary'); ?>">
                                                    <?php 
                                                    $statusLabels = [
                                                        'active' => 'Активен',
                                                        'used' => 'Использован',
                                                        'cancelled' => 'Отменен'
                                                    ];
                                                    echo $statusLabels[$ticket['status']] ?? $ticket['status'];
                                                    ?>
                                                </span>
                                            </div>
                                            
                                            <div class="row mb-3">
                                                <div class="col-6">
                                                    <small class="text-muted">Дата:</small><br>
                                                    <strong><?php echo formatDate($ticket['event_date'], 'd.m.Y'); ?></strong>
                                                </div>
                                                <div class="col-6">
                                                    <small class="text-muted">Время:</small><br>
                                                    <strong><?php echo formatDate($ticket['event_date'], 'H:i'); ?></strong>
                                                </div>
                                            </div>
                                            
                                            <div class="row mb-3">
                                                <div class="col-6">
                                                    <small class="text-muted">Место:</small><br>
                                                    <strong><?php echo htmlspecialchars($ticket['venue']); ?></strong>
                                                </div>
                                                <div class="col-6">
                                                    <small class="text-muted">Место:</small><br>
                                                    <strong>Ряд <?php echo htmlspecialchars($ticket['row_number']); ?>, Место <?php echo htmlspecialchars($ticket['seat_number']); ?></strong>
                                                </div>
                                            </div>
                                            
                                            <?php if ($ticket['section']): ?>
                                                <div class="mb-3">
                                                    <small class="text-muted">Сектор:</small><br>
                                                    <strong><?php echo htmlspecialchars($ticket['section']); ?></strong>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <div class="mb-3">
                                                <small class="text-muted">Категория:</small><br>
                                                <strong><?php echo htmlspecialchars($ticket['category_name']); ?></strong>
                                            </div>
                                            
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <small class="text-muted">Код билета:</small><br>
                                                    <code><?php echo htmlspecialchars($ticket['ticket_code']); ?></code>
                                                </div>
                                                <div class="text-end">
                                                    <div class="h6 text-primary mb-0"><?php echo formatPrice($ticket['price']); ?></div>
                                                </div>
                                            </div>
                                            
                                            <?php if ($ticket['status'] === 'active'): ?>
                                                <div class="mt-3">
                                                    <button class="btn btn-outline-primary btn-sm" onclick="showQRCode('<?php echo $ticket['qr_code']; ?>')">
                                                        <i class="fas fa-qrcode me-1"></i>
                                                        Показать QR-код
                                                    </button>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <?php if ($ticket['used_at']): ?>
                                                <div class="mt-2">
                                                    <small class="text-muted">
                                                        <i class="fas fa-check-circle me-1"></i>
                                                        Использован: <?php echo formatDate($ticket['used_at'], 'd.m.Y H:i'); ?>
                                                    </small>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно для QR-кода -->
<div class="modal fade" id="qrModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">QR-код билета</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="qrImage" src="" alt="QR-код" class="img-fluid">
                <p class="mt-3 text-muted">Покажите этот QR-код на входе</p>
            </div>
        </div>
    </div>
</div>

<script>
function showQRCode(qrCodeUrl) {
    document.getElementById('qrImage').src = qrCodeUrl;
    new bootstrap.Modal(document.getElementById('qrModal')).show();
}
</script>

<?php
$content = ob_get_clean();
$pageTitle = 'Мои билеты';

include 'header.php';
?>
