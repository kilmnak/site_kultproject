<?php
// admin/users.php - Управление пользователями
$db = Database::getInstance();

$action = $_GET['action'] ?? 'list';
$userId = intval($_GET['id'] ?? 0);
$message = '';
$messageType = '';

// Обработка POST запросов
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'update_role') {
            $userId = intval($_POST['user_id']);
            $newRole = $_POST['role'];
            
            // Защита от изменения роли самого себя
            if ($userId === $_SESSION['user_id']) {
                $message = 'Вы не можете изменить свою собственную роль.';
                $messageType = 'warning';
            } else {
                $db->query("UPDATE users SET role = ? WHERE id = ?", [$newRole, $userId]);
                $message = 'Роль пользователя успешно обновлена!';
                $messageType = 'success';
            }
        } elseif ($_POST['action'] === 'toggle_status') {
            $userId = intval($_POST['user_id']);
            
            // Защита от блокировки самого себя
            if ($userId === $_SESSION['user_id']) {
                $message = 'Вы не можете заблокировать самого себя.';
                $messageType = 'warning';
            } else {
                $db->query("UPDATE users SET is_active = NOT is_active WHERE id = ?", [$userId]);
                $message = 'Статус пользователя успешно изменен!';
                $messageType = 'success';
            }
        }
    } elseif (isset($_POST['delete_id'])) {
        $deleteId = intval($_POST['delete_id']);
        
        // Защита от удаления самого себя
        if ($deleteId === $_SESSION['user_id']) {
            $message = 'Вы не можете удалить самого себя.';
            $messageType = 'warning';
        } else {
            $db->query("DELETE FROM users WHERE id = ?", [$deleteId]);
            $message = 'Пользователь успешно удален!';
            $messageType = 'success';
        }
        $action = 'list';
    }
}

// Получаем пользователей
$filter = $_GET['filter'] ?? 'all';
$whereClause = '';
$params = [];

switch ($filter) {
    case 'clients':
        $whereClause = "WHERE role = 'client'";
        break;
    case 'managers':
        $whereClause = "WHERE role = 'manager'";
        break;
    case 'admins':
        $whereClause = "WHERE role = 'admin'";
        break;
    case 'active':
        $whereClause = "WHERE is_active = 1";
        break;
    case 'inactive':
        $whereClause = "WHERE is_active = 0";
        break;
}

$users = $db->fetchAll("SELECT * FROM users $whereClause ORDER BY created_at DESC");

// Статистика пользователей
$userStats = [
    'total' => $db->fetch("SELECT COUNT(*) as count FROM users")['count'],
    'clients' => $db->fetch("SELECT COUNT(*) as count FROM users WHERE role = 'client'")['count'],
    'managers' => $db->fetch("SELECT COUNT(*) as count FROM users WHERE role = 'manager'")['count'],
    'admins' => $db->fetch("SELECT COUNT(*) as count FROM users WHERE role = 'admin'")['count'],
    'active' => $db->fetch("SELECT COUNT(*) as count FROM users WHERE is_active = 1")['count'],
    'inactive' => $db->fetch("SELECT COUNT(*) as count FROM users WHERE is_active = 0")['count'],
];

ob_start();
?>

<div class="row">
    <div class="col-12">
        <h1 class="h3 mb-4 text-gray-800">Управление пользователями</h1>
    </div>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
        <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
        <?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Статистика -->
<div class="row mb-4">
    <div class="col-xl-2 col-md-4 mb-4">
        <div class="card stats-card">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Всего
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $userStats['total']; ?></div>
                    </div>
                    <div class="col-auto">
                        <div class="stats-icon bg-primary">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-4 mb-4">
        <div class="card stats-card">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Клиенты
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $userStats['clients']; ?></div>
                    </div>
                    <div class="col-auto">
                        <div class="stats-icon bg-info">
                            <i class="fas fa-user"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-4 mb-4">
        <div class="card stats-card">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Менеджеры
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $userStats['managers']; ?></div>
                    </div>
                    <div class="col-auto">
                        <div class="stats-icon bg-warning">
                            <i class="fas fa-user-tie"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-4 mb-4">
        <div class="card stats-card">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                            Админы
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $userStats['admins']; ?></div>
                    </div>
                    <div class="col-auto">
                        <div class="stats-icon bg-danger">
                            <i class="fas fa-crown"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-4 mb-4">
        <div class="card stats-card">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Активные
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $userStats['active']; ?></div>
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

    <div class="col-xl-2 col-md-4 mb-4">
        <div class="card stats-card">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">
                            Заблокированные
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $userStats['inactive']; ?></div>
                    </div>
                    <div class="col-auto">
                        <div class="stats-icon bg-secondary">
                            <i class="fas fa-ban"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Фильтры -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="btn-group" role="group">
                    <a href="?action=list&filter=all" class="btn btn-outline-primary <?php echo $filter === 'all' ? 'active' : ''; ?>">Все</a>
                    <a href="?action=list&filter=clients" class="btn btn-outline-info <?php echo $filter === 'clients' ? 'active' : ''; ?>">Клиенты</a>
                    <a href="?action=list&filter=managers" class="btn btn-outline-warning <?php echo $filter === 'managers' ? 'active' : ''; ?>">Менеджеры</a>
                    <a href="?action=list&filter=admins" class="btn btn-outline-danger <?php echo $filter === 'admins' ? 'active' : ''; ?>">Админы</a>
                    <a href="?action=list&filter=active" class="btn btn-outline-success <?php echo $filter === 'active' ? 'active' : ''; ?>">Активные</a>
                    <a href="?action=list&filter=inactive" class="btn btn-outline-secondary <?php echo $filter === 'inactive' ? 'active' : ''; ?>">Заблокированные</a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Поиск пользователей..." id="searchInput">
                    <button class="btn btn-outline-secondary" type="button">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Таблица пользователей -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
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
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="fas fa-users fa-3x mb-3"></i>
                                <p>Пользователей не найдено</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar bg-<?php echo match($user['role']) {
                                            'admin' => 'danger',
                                            'manager' => 'warning',
                                            'client' => 'info',
                                            default => 'secondary'
                                        }; ?> text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                            <i class="fas fa-<?php echo match($user['role']) {
                                                'admin' => 'crown',
                                                'manager' => 'user-tie',
                                                'client' => 'user',
                                                default => 'user'
                                            }; ?>"></i>
                                        </div>
                                        <div>
                                            <strong><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></strong>
                                            <?php if ($user['id'] === $_SESSION['user_id']): ?>
                                                <span class="badge bg-primary ms-2">Вы</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo match($user['role']) {
                                        'admin' => 'danger',
                                        'manager' => 'warning',
                                        'client' => 'info',
                                        default => 'secondary'
                                    }; ?>">
                                        <?php echo match($user['role']) {
                                            'admin' => 'Администратор',
                                            'manager' => 'Менеджер',
                                            'client' => 'Клиент',
                                            default => $user['role']
                                        }; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $user['is_active'] ? 'success' : 'secondary'; ?>">
                                        <?php echo $user['is_active'] ? 'Активен' : 'Заблокирован'; ?>
                                    </span>
                                </td>
                                <td><?php echo formatDate($user['created_at'], 'd.m.Y'); ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <!-- Изменение роли -->
                                        <div class="dropdown">
                                            <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                <i class="fas fa-user-cog"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><h6 class="dropdown-header">Изменить роль</h6></li>
                                                <li>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="action" value="update_role">
                                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                        <button type="submit" name="role" value="client" class="dropdown-item <?php echo $user['role'] === 'client' ? 'active' : ''; ?>">
                                                            <i class="fas fa-user me-2"></i>Клиент
                                                        </button>
                                                    </form>
                                                </li>
                                                <li>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="action" value="update_role">
                                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                        <button type="submit" name="role" value="manager" class="dropdown-item <?php echo $user['role'] === 'manager' ? 'active' : ''; ?>">
                                                            <i class="fas fa-user-tie me-2"></i>Менеджер
                                                        </button>
                                                    </form>
                                                </li>
                                                <li>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="action" value="update_role">
                                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                        <button type="submit" name="role" value="admin" class="dropdown-item <?php echo $user['role'] === 'admin' ? 'active' : ''; ?>">
                                                            <i class="fas fa-crown me-2"></i>Администратор
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                        
                                        <!-- Блокировка/разблокировка -->
                                        <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="toggle_status">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <button type="submit" class="btn btn-outline-<?php echo $user['is_active'] ? 'warning' : 'success'; ?>" 
                                                        title="<?php echo $user['is_active'] ? 'Заблокировать' : 'Разблокировать'; ?>">
                                                    <i class="fas fa-<?php echo $user['is_active'] ? 'ban' : 'check'; ?>"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        
                                        <!-- Удаление -->
                                        <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                                            <button type="button" class="btn btn-outline-danger" onclick="confirmDelete(<?php echo $user['id']; ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Модальное окно подтверждения удаления -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Подтверждение удаления</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Вы уверены, что хотите удалить этого пользователя?</p>
                <p class="text-danger"><strong>Это действие нельзя отменить!</strong></p>
                <p class="text-muted">Все связанные данные (бронирования, билеты) также будут удалены.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="delete_id" id="deleteId">
                    <button type="submit" class="btn btn-danger">Удалить</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(userId) {
    document.getElementById('deleteId').value = userId;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

// Поиск в таблице
document.getElementById('searchInput').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const tableRows = document.querySelectorAll('tbody tr');
    
    tableRows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});
</script>

<?php
$content = ob_get_clean();
$pageTitle = 'Управление пользователями';
include 'admin-header.php';
?>
