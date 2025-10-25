<?php
require_once 'config.php';
require_once 'includes/functions.php';

session_start();

$auth = new Auth();
$auth->requireLogin();

$db = Database::getInstance();
$user = $auth->getCurrentUser();

// Обработка обновления профиля
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $firstName = sanitizeInput($_POST['first_name'] ?? '');
    $lastName = sanitizeInput($_POST['last_name'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    
    if (empty($firstName) || empty($lastName)) {
        showMessage('Заполните обязательные поля', 'error');
    } else {
        $db->query(
            "UPDATE users SET first_name = ?, last_name = ?, phone = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?",
            [$firstName, $lastName, $phone, $user['id']]
        );
        showMessage('Профиль успешно обновлен', 'success');
        $user = $auth->getCurrentUser(); // Обновляем данные пользователя
    }
}

// Обработка смены пароля
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        showMessage('Заполните все поля', 'error');
    } elseif ($newPassword !== $confirmPassword) {
        showMessage('Новые пароли не совпадают', 'error');
    } elseif (strlen($newPassword) < 6) {
        showMessage('Новый пароль должен содержать минимум 6 символов', 'error');
    } elseif (!password_verify($currentPassword, $user['password'])) {
        showMessage('Текущий пароль неверен', 'error');
    } else {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $db->query(
            "UPDATE users SET password = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?",
            [$hashedPassword, $user['id']]
        );
        showMessage('Пароль успешно изменен', 'success');
    }
}

// Получаем статистику пользователя
$userStats = $db->fetch(
    "SELECT 
        COUNT(DISTINCT b.id) as total_bookings,
        COUNT(DISTINCT t.id) as total_tickets,
        SUM(p.amount) as total_spent
     FROM users u
     LEFT JOIN bookings b ON u.id = b.user_id AND b.status = 'confirmed'
     LEFT JOIN ticket_orders `to` ON b.id = `to`.booking_id
     LEFT JOIN tickets t ON `to`.id = t.order_id
     LEFT JOIN payments p ON b.id = p.booking_id AND p.payment_status = 'completed'
     WHERE u.id = ?",
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
                        <a class="nav-link active" href="/profile.php">
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
                        <a class="nav-link" href="/notifications.php">
                            <i class="fas fa-bell me-2"></i>
                            Уведомления
                            <?php
                            $unreadCount = $db->fetch(
                                "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND status = 'pending'",
                                [$user['id']]
                            )['count'];
                            if ($unreadCount > 0):
                            ?>
                                <span class="badge bg-danger ms-2"><?php echo $unreadCount; ?></span>
                            <?php endif; ?>
                        </a>
                    </nav>
                </div>
            </div>
            
            <!-- Статистика -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0">Статистика</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Бронирований:</span>
                        <strong><?php echo $userStats['total_bookings'] ?? 0; ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Билетов:</span>
                        <strong><?php echo $userStats['total_tickets'] ?? 0; ?></strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Потрачено:</span>
                        <strong><?php echo formatPrice($userStats['total_spent'] ?? 0); ?></strong>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-9">
            <!-- Профиль -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Личная информация</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="update_profile">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">Имя</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" 
                                       value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">Фамилия</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" 
                                       value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                            <div class="form-text">Email нельзя изменить</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="phone" class="form-label">Телефон</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="<?php echo htmlspecialchars($user['phone']); ?>">
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>
                            Сохранить изменения
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Смена пароля -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Смена пароля</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="change_password">
                        
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Текущий пароль</label>
                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="new_password" class="form-label">Новый пароль</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                            <div class="form-text">Минимум 6 символов</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Подтверждение пароля</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                        
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-key me-2"></i>
                            Изменить пароль
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = 'Личный кабинет';

include 'header.php';
?>
