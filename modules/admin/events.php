<?php
require_once '../config.php';
require_once '../includes/functions.php';

session_start();

$auth = new Auth();
$auth->requireRole('admin');

$db = Database::getInstance();
$eventManager = new EventManager();

$action = $_GET['action'] ?? 'list';
$eventId = intval($_GET['id'] ?? 0);
$message = '';
$messageType = '';

// Обработка действий
if ($_POST) {
    switch ($_POST['action']) {
        case 'create':
            $data = [
                'title' => sanitizeInput($_POST['title']),
                'description' => sanitizeInput($_POST['description']),
                'event_date' => $_POST['event_date'],
                'venue' => sanitizeInput($_POST['venue']),
                'address' => sanitizeInput($_POST['address']),
                'max_capacity' => intval($_POST['max_capacity']),
                'base_price' => floatval($_POST['base_price']),
                'status' => $_POST['status'],
                'image_url' => sanitizeInput($_POST['image_url'])
            ];
            
            if ($eventManager->createEvent($data)) {
                $message = 'Мероприятие успешно создано';
                $messageType = 'success';
            } else {
                $message = 'Ошибка при создании мероприятия';
                $messageType = 'danger';
            }
            break;
            
        case 'update':
            $data = [
                'title' => sanitizeInput($_POST['title']),
                'description' => sanitizeInput($_POST['description']),
                'event_date' => $_POST['event_date'],
                'venue' => sanitizeInput($_POST['venue']),
                'address' => sanitizeInput($_POST['address']),
                'max_capacity' => intval($_POST['max_capacity']),
                'base_price' => floatval($_POST['base_price']),
                'status' => $_POST['status'],
                'image_url' => sanitizeInput($_POST['image_url'])
            ];
            
            if ($eventManager->updateEvent($eventId, $data)) {
                $message = 'Мероприятие успешно обновлено';
                $messageType = 'success';
            } else {
                $message = 'Ошибка при обновлении мероприятия';
                $messageType = 'danger';
            }
            break;
            
        case 'delete':
            if ($eventManager->deleteEvent($eventId)) {
                $message = 'Мероприятие успешно удалено';
                $messageType = 'success';
            } else {
                $message = 'Ошибка при удалении мероприятия';
                $messageType = 'danger';
            }
            break;
    }
}

// Получаем мероприятие для редактирования
$event = null;
if ($eventId && $action === 'edit') {
    $event = $eventManager->getEvent($eventId);
    if (!$event) {
        $message = 'Мероприятие не найдено';
        $messageType = 'danger';
        $action = 'list';
    }
}

// Получаем список мероприятий
$events = $eventManager->getEvents();

ob_start();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Управление мероприятиями</h1>
                <a href="?action=create" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>
                    Добавить мероприятие
                </a>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($action === 'create' || $action === 'edit'): ?>
        <!-- Форма создания/редактирования -->
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-<?php echo $action === 'create' ? 'plus' : 'edit'; ?> me-2"></i>
                            <?php echo $action === 'create' ? 'Создание мероприятия' : 'Редактирование мероприятия'; ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="<?php echo $action; ?>">
                            
                            <div class="row">
                                <div class="col-md-8 mb-3">
                                    <label for="title" class="form-label">Название мероприятия *</label>
                                    <input type="text" class="form-control" id="title" name="title" 
                                           value="<?php echo htmlspecialchars($event['title'] ?? ''); ?>" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="status" class="form-label">Статус</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="draft" <?php echo ($event['status'] ?? '') === 'draft' ? 'selected' : ''; ?>>Черновик</option>
                                        <option value="published" <?php echo ($event['status'] ?? '') === 'published' ? 'selected' : ''; ?>>Опубликовано</option>
                                        <option value="cancelled" <?php echo ($event['status'] ?? '') === 'cancelled' ? 'selected' : ''; ?>>Отменено</option>
                                        <option value="completed" <?php echo ($event['status'] ?? '') === 'completed' ? 'selected' : ''; ?>>Завершено</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Описание</label>
                                <textarea class="form-control" id="description" name="description" rows="4"><?php echo htmlspecialchars($event['description'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="event_date" class="form-label">Дата и время *</label>
                                    <input type="datetime-local" class="form-control" id="event_date" name="event_date" 
                                           value="<?php echo $event['event_date'] ?? ''; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="venue" class="form-label">Место проведения *</label>
                                    <input type="text" class="form-control" id="venue" name="venue" 
                                           value="<?php echo htmlspecialchars($event['venue'] ?? ''); ?>" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="address" class="form-label">Адрес</label>
                                <input type="text" class="form-control" id="address" name="address" 
                                       value="<?php echo htmlspecialchars($event['address'] ?? ''); ?>">
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="max_capacity" class="form-label">Максимальная вместимость *</label>
                                    <input type="number" class="form-control" id="max_capacity" name="max_capacity" 
                                           value="<?php echo $event['max_capacity'] ?? ''; ?>" min="1" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="base_price" class="form-label">Базовая цена (руб.) *</label>
                                    <input type="number" class="form-control" id="base_price" name="base_price" 
                                           value="<?php echo $event['base_price'] ?? ''; ?>" min="0" step="0.01" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="image_url" class="form-label">URL изображения</label>
                                <input type="url" class="form-control" id="image_url" name="image_url" 
                                       value="<?php echo htmlspecialchars($event['image_url'] ?? ''); ?>">
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>
                                    <?php echo $action === 'create' ? 'Создать' : 'Сохранить'; ?>
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
                        <h6 class="card-title mb-0">Статистика мероприятий</h6>
                    </div>
                    <div class="card-body">
                        <?php
                        $stats = [
                            'total' => count($events),
                            'published' => count(array_filter($events, fn($e) => $e['status'] === 'published')),
                            'draft' => count(array_filter($events, fn($e) => $e['status'] === 'draft')),
                            'cancelled' => count(array_filter($events, fn($e) => $e['status'] === 'cancelled'))
                        ];
                        ?>
                        <div class="row text-center">
                            <div class="col-6 mb-3">
                                <div class="border rounded p-2">
                                    <h4 class="text-primary mb-0"><?php echo $stats['total']; ?></h4>
                                    <small class="text-muted">Всего</small>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="border rounded p-2">
                                    <h4 class="text-success mb-0"><?php echo $stats['published']; ?></h4>
                                    <small class="text-muted">Опубликовано</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-2">
                                    <h4 class="text-warning mb-0"><?php echo $stats['draft']; ?></h4>
                                    <small class="text-muted">Черновики</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-2">
                                    <h4 class="text-danger mb-0"><?php echo $stats['cancelled']; ?></h4>
                                    <small class="text-muted">Отменено</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    <?php elseif ($action === 'delete'): ?>
        <!-- Подтверждение удаления -->
        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Подтверждение удаления
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php $event = $eventManager->getEvent($eventId); ?>
                        <?php if ($event): ?>
                            <p>Вы уверены, что хотите удалить мероприятие:</p>
                            <div class="alert alert-warning">
                                <strong><?php echo htmlspecialchars($event['title']); ?></strong><br>
                                <small><?php echo date('d.m.Y H:i', strtotime($event['event_date'])); ?> - <?php echo htmlspecialchars($event['venue']); ?></small>
                            </div>
                            <p class="text-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Внимание:</strong> Это действие нельзя отменить. Все связанные данные (бронирования, билеты) также будут удалены.
                            </p>
                            
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="action" value="delete">
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-trash me-2"></i>
                                    Да, удалить
                                </button>
                            </form>
                            <a href="?action=list" class="btn btn-secondary ms-2">
                                <i class="fas fa-times me-2"></i>
                                Отмена
                            </a>
                        <?php else: ?>
                            <div class="alert alert-danger">
                                Мероприятие не найдено
                            </div>
                            <a href="?action=list" class="btn btn-primary">Вернуться к списку</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
    <?php else: ?>
        <!-- Список мероприятий -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Список мероприятий</h5>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="filterEvents('all')">Все</button>
                                <button type="button" class="btn btn-outline-success btn-sm" onclick="filterEvents('published')">Опубликовано</button>
                                <button type="button" class="btn btn-outline-warning btn-sm" onclick="filterEvents('draft')">Черновики</button>
                                <button type="button" class="btn btn-outline-danger btn-sm" onclick="filterEvents('cancelled')">Отменено</button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Название</th>
                                        <th>Дата</th>
                                        <th>Место</th>
                                        <th>Вместимость</th>
                                        <th>Цена</th>
                                        <th>Статус</th>
                                        <th>Действия</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($events)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-4">
                                                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                                <p class="text-muted">Мероприятия не найдены</p>
                                                <a href="?action=create" class="btn btn-primary">
                                                    <i class="fas fa-plus me-2"></i>
                                                    Создать первое мероприятие
                                                </a>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($events as $event): ?>
                                            <tr data-status="<?php echo $event['status']; ?>">
                                                <td>
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($event['title']); ?></strong>
                                                        <?php if ($event['description']): ?>
                                                            <br><small class="text-muted"><?php echo htmlspecialchars(substr($event['description'], 0, 100)) . (strlen($event['description']) > 100 ? '...' : ''); ?></small>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div>
                                                        <?php echo date('d.m.Y', strtotime($event['event_date'])); ?>
                                                        <br><small class="text-muted"><?php echo date('H:i', strtotime($event['event_date'])); ?></small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div>
                                                        <?php echo htmlspecialchars($event['venue']); ?>
                                                        <?php if ($event['address']): ?>
                                                            <br><small class="text-muted"><?php echo htmlspecialchars($event['address']); ?></small>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td><?php echo number_format($event['max_capacity']); ?></td>
                                                <td><?php echo number_format($event['base_price'], 0, ',', ' '); ?> ₽</td>
                                                <td>
                                                    <?php
                                                    $statusClasses = [
                                                        'draft' => 'warning',
                                                        'published' => 'success',
                                                        'cancelled' => 'danger',
                                                        'completed' => 'info'
                                                    ];
                                                    $statusLabels = [
                                                        'draft' => 'Черновик',
                                                        'published' => 'Опубликовано',
                                                        'cancelled' => 'Отменено',
                                                        'completed' => 'Завершено'
                                                    ];
                                                    ?>
                                                    <span class="badge bg-<?php echo $statusClasses[$event['status']]; ?>">
                                                        <?php echo $statusLabels[$event['status']]; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <a href="../event-details.php?id=<?php echo $event['id']; ?>" 
                                                           class="btn btn-outline-primary" title="Просмотр">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="?action=edit&id=<?php echo $event['id']; ?>" 
                                                           class="btn btn-outline-success" title="Редактировать">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="?action=delete&id=<?php echo $event['id']; ?>" 
                                                           class="btn btn-outline-danger" title="Удалить"
                                                           onclick="return confirm('Вы уверены, что хотите удалить это мероприятие?')">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
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
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function filterEvents(status) {
    const rows = document.querySelectorAll('tbody tr[data-status]');
    rows.forEach(row => {
        if (status === 'all' || row.dataset.status === status) {
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
$pageTitle = 'Управление мероприятиями';

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
