<?php
require_once '../config.php';
require_once '../includes/functions.php';

session_start();

$auth = new Auth();
$auth->requireRole('admin');

$db = Database::getInstance();

$action = $_GET['action'] ?? 'list';
$userId = intval($_GET['id'] ?? 0);
$message = '';
$messageType = '';

// Обработка действий
if ($_POST) {
    switch ($_POST['action']) {
        case 'update_role':
            $newRole = $_POST['role'];
            $db->query("UPDATE users SET role = ? WHERE id = ?", [$newRole, $userId]);
            $message = 'Роль пользователя обновлена';
            $messageType = 'success';
            break;
            
        case 'toggle_status':
            $isActive = $_POST['is_active'] === '1';
            $db->query("UPDATE users SET is_active = ? WHERE id = ?", [$isActive, $userId]);
            $message = 'Статус пользователя обновлен';
            $messageType = 'success';
            break;
            
        case 'delete':
            // Проверяем, что это не администратор
            $user = $db->fetch("SELECT role FROM users WHERE id = ?", [$userId]);
            if ($user && $user['role'] !== 'admin') {
                $db->query("DELETE FROM users WHERE id = ?", [$userId]);
                $message = 'Пользователь удален';
                $messageType = 'success';
            } else {
                $message = 'Нельзя удалить администратора';
                $messageType = 'danger';
            }
            break;
    }
}

// Получаем пользователя для редактирования
$user = null;
if ($userId && $action === 'edit') {
    $user = $db->fetch("SELECT * FROM users WHERE id = ?", [$userId]);
    if (!$user) {
        $message = 'Пользователь не найден';
        $messageType = 'danger';
        $action = 'list';
    }
}

// Получаем список пользователей
$users = $db->fetchAll("SELECT * FROM users ORDER BY created_at DESC");

ob_start();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="h3 mb-4">Управление пользователями</h1>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($action === 'edit'): ?>
        <!-- Форма редактирования пользователя -->
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-user-edit me-2"></i>
                            Редактирование пользователя
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="update_role">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Имя</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['first_name']); ?>" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Фамилия</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['last_name']); ?>" readonly>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="role" class="form-label">Роль</label>
                                    <select class="form-select" id="role" name="role">
                                        <option value="client" <?php echo $user['role'] === 'client' ? 'selected' : ''; ?>>Клиент</option>
                                        <option value="manager" <?php echo $user['role'] === 'manager' ? 'selected' : ''; ?>>Менеджер</option>
                                        <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Администратор</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="is_active" class="form-label">Статус</label>
                                    <select class="form-select" id="is_active" name="is_active">
                                        <option value="1" <?php echo $user['is_active'] ? 'selected' : ''; ?>>Активен</option>
                                        <option value="0" <?php echo !$user['is_active'] ? 'selected' : ''; ?>>Заблокирован</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Дата регистрации</label>
                                <input type="text" class="form-control" value="<?php echo date('d.m.Y H:i', strtotime($user['created_at'])); ?>" readonly>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>
                                    Сохранить изменения
                                </button>
                                <a href="?action=list" class="btn btn-secondary">
                                    <i class="fas fa-times me-2"></i>
                                    Отмена
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title mb-0">Статистика пользователя</h6>
                    </div>
                    <div class="card-body">
                        <?php
                        $userStats = [
                            'bookings' => $db->fetch("SELECT COUNT(*) as count FROM bookings WHERE user_id = ?", [$userId])['count'],
                            'tickets' => $db->fetch("SELECT COUNT(*) as count FROM ticket_orders to JOIN bookings b ON to.booking_id = b.id WHERE b.user_id = ?", [$userId])['count'],
                            'total_spent' => $db->fetch("SELECT SUM(p.amount) as total FROM payments p JOIN bookings b ON p.booking_id = b.id WHERE b.user_id = ? AND p.payment_status = 'completed'", [$userId])['total'] ?? 0
                        ];
                        ?>
                        <div class="row text-center">
                            <div class="col-12 mb-3">
                                <div class="border rounded p-2">
                                    <h4 class="text-primary mb-0"><?php echo $userStats['bookings']; ?></h4>
                                    <small class="text-muted">Бронирований</small>
                                </div>
                            </div>
                            <div class="col-12 mb-3">
                                <div class="border rounded p-2">
                                    <h4 class="text-success mb-0"><?php echo $userStats['tickets']; ?></h4>
                                    <small class="text-muted">Билетов</small>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="border rounded p-2">
                                    <h4 class="text-info mb-0"><?php echo number_format($userStats['total_spent'], 0, ',', ' '); ?> ₽</h4>
                                    <small class="text-muted">Потрачено</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    <?php else: ?>
        <!-- Список пользователей -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Список пользователей</h5>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="filterUsers('all')">Все</button>
                                <button type="button" class="btn btn-outline-success btn-sm" onclick="filterUsers('client')">Клиенты</button>
                                <button type="button" class="btn btn-outline-warning btn-sm" onclick="filterUsers('manager')">Менеджеры</button>
                                <button type="button" class="btn btn-outline-danger btn-sm" onclick="filterUsers('admin')">Админы</button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Пользователь</th>
                                        <th>Email</th>
                                        <th>Роль</th>
                                        <th>Статус</th>
                                        <th>Регистрация</th>
                                        <th>Действия</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user): ?>
                                        <tr data-role="<?php echo $user['role']; ?>">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar bg-primary text-white rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                        <?php echo strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)); ?>
                                                    </div>
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></strong>
                                                        <?php if ($user['phone']): ?>
                                                            <br><small class="text-muted"><?php echo htmlspecialchars($user['phone']); ?></small>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td>
                                                <?php
                                                $roleClasses = [
                                                    'client' => 'primary',
                                                    'manager' => 'warning',
                                                    'admin' => 'danger'
                                                ];
                                                $roleLabels = [
                                                    'client' => 'Клиент',
                                                    'manager' => 'Менеджер',
                                                    'admin' => 'Администратор'
                                                ];
                                                ?>
                                                <span class="badge bg-<?php echo $roleClasses[$user['role']]; ?>">
                                                    <?php echo $roleLabels[$user['role']]; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($user['is_active']): ?>
                                                    <span class="badge bg-success">Активен</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Заблокирован</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div>
                                                    <?php echo date('d.m.Y', strtotime($user['created_at'])); ?>
                                                    <br><small class="text-muted"><?php echo date('H:i', strtotime($user['created_at'])); ?></small>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="?action=edit&id=<?php echo $user['id']; ?>" 
                                                       class="btn btn-outline-primary" title="Редактировать">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <?php if ($user['role'] !== 'admin'): ?>
                                                        <a href="?action=delete&id=<?php echo $user['id']; ?>" 
                                                           class="btn btn-outline-danger" title="Удалить"
                                                           onclick="return confirm('Вы уверены, что хотите удалить этого пользователя?')">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function filterUsers(role) {
    const rows = document.querySelectorAll('tbody tr[data-role]');
    rows.forEach(row => {
        if (role === 'all' || row.dataset.role === role) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
    
    // Обновляем активную кнопку
    document.querySelectorAll('.btn-group .btn').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');
}
</script>

<?php
$content = ob_get_clean();
$pageTitle = 'Управление пользователями';

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
