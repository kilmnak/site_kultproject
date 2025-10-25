<?php
require_once 'config.php';
require_once 'includes/functions.php';

session_start();

$auth = new Auth();
$auth->requireLogin();

$db = Database::getInstance();
$user = $auth->getCurrentUser();

// Получаем уведомления пользователя
$notifications = $db->fetchAll(
    "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 50",
    [$user['id']]
);

// Отмечаем уведомления как прочитанные
if (!empty($notifications)) {
    $db->query(
        "UPDATE notifications SET status = 'sent' WHERE user_id = ? AND status = 'pending'",
        [$user['id']]
    );
}

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
                        <a class="nav-link" href="/my-bookings.php">
                            <i class="fas fa-calendar-check me-2"></i>
                            Мои бронирования
                        </a>
                        <a class="nav-link active" href="/notifications.php">
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
                    <h5 class="mb-0">Уведомления</h5>
                    <span class="badge bg-primary"><?php echo count($notifications); ?> уведомлений</span>
                </div>
                <div class="card-body">
                    <?php if (empty($notifications)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-bell-slash text-muted display-1 mb-3"></i>
                            <h5 class="text-muted">Нет уведомлений</h5>
                            <p class="text-muted">Здесь будут появляться важные уведомления о ваших билетах и мероприятиях</p>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($notifications as $notification): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-start">
                                    <div class="ms-2 me-auto">
                                        <div class="fw-bold">
                                            <i class="fas fa-<?php 
                                                echo $notification['type'] === 'email' ? 'envelope' : 
                                                    ($notification['type'] === 'sms' ? 'sms' : 'bell'); 
                                            ?> me-2"></i>
                                            <?php echo htmlspecialchars($notification['subject']); ?>
                                        </div>
                                        <p class="mb-1"><?php echo nl2br(htmlspecialchars($notification['message'])); ?></p>
                                        <small class="text-muted">
                                            <?php echo formatDate($notification['created_at'], 'd.m.Y H:i'); ?>
                                        </small>
                                    </div>
                                    <span class="badge bg-<?php 
                                        echo $notification['status'] === 'sent' ? 'success' : 
                                            ($notification['status'] === 'failed' ? 'danger' : 'warning'); 
                                    ?> rounded-pill">
                                        <?php 
                                        $statusLabels = [
                                            'pending' => 'Ожидает',
                                            'sent' => 'Отправлено',
                                            'failed' => 'Ошибка'
                                        ];
                                        echo $statusLabels[$notification['status']] ?? $notification['status'];
                                        ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = 'Уведомления';

include 'header.php';
?>
