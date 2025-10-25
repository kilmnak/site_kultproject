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

// Обработка создания/редактирования мероприятия
if ($_POST && isset($_POST['action'])) {
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
    
    if ($_POST['action'] === 'create') {
        $eventId = $eventManager->createEvent($data);
        showMessage('Мероприятие успешно создано', 'success');
        redirect('events.php?id=' . $eventId);
    } elseif ($_POST['action'] === 'update') {
        $eventManager->updateEvent($eventId, $data);
        showMessage('Мероприятие успешно обновлено', 'success');
        redirect('events.php?id=' . $eventId);
    }
}

// Получаем мероприятие для редактирования
$event = null;
if ($eventId) {
    $event = $eventManager->getEvent($eventId);
}

// Получаем список мероприятий
$events = $eventManager->getEvents();

ob_start();
?>

<div class="container-fluid my-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Управление мероприятиями</h1>
                <a href="?action=create" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>
                    Создать мероприятие
                </a>
            </div>
        </div>
    </div>
    
    <?php if ($action === 'create' || $action === 'edit'): ?>
        <!-- Форма создания/редактирования -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <?php echo $action === 'create' ? 'Создание мероприятия' : 'Редактирование мероприятия'; ?>
                        </h6>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="<?php echo $action === 'create' ? 'create' : 'update'; ?>">
                            
                            <div class="row">
                                <div class="col-md-8 mb-3">
                                    <label for="title" class="form-label">Название мероприятия *</label>
                                    <input type="text" class="form-control" id="title" name="title" 
                                           value="<?php echo htmlspecialchars($event['title'] ?? ''); ?>" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="status" class="form-label">Статус</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="draft" <?php echo ($event['status'] ?? 'draft') === 'draft' ? 'selected' : ''; ?>>Черновик</option>
                                        <option value="published" <?php echo ($event['status'] ?? '') === 'published' ? 'selected' : ''; ?>>Опубликовано</option>
                                        <option value="cancelled" <?php echo ($event['status'] ?? '') === 'cancelled' ? 'selected' : ''; ?>>Отменено</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Описание</label>
                                <textarea class="form-control" id="description" name="description" rows="4"><?php echo htmlspecialchars($event['description'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="event_date" class="form-label">Дата и время проведения *</label>
                                    <input type="datetime-local" class="form-control" id="event_date" name="event_date" 
                                           value="<?php echo $event['event_date'] ? date('Y-m-d\TH:i', strtotime($event['event_date'])) : ''; ?>" required>
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
                                           value="<?php echo $event['max_capacity'] ?? ''; ?>" required min="1">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="base_price" class="form-label">Базовая цена (₽) *</label>
                                    <input type="number" class="form-control" id="base_price" name="base_price" 
                                           value="<?php echo $event['base_price'] ?? ''; ?>" required min="0" step="0.01">
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
                                <a href="events.php" class="btn btn-secondary">Отмена</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Список мероприятий -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-primary">Все мероприятия</h6>
                    </div>
                    <div class="card-body">
                        <?php if (empty($events)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-calendar text-muted fa-3x mb-3"></i>
                                <h5 class="text-muted">Нет мероприятий</h5>
                                <p class="text-muted">Создайте первое мероприятие</p>
                                <a href="?action=create" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>
                                    Создать мероприятие
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
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
                                        <?php foreach ($events as $event): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($event['title']); ?></strong>
                                                </td>
                                                <td><?php echo formatDate($event['event_date'], 'd.m.Y H:i'); ?></td>
                                                <td><?php echo htmlspecialchars($event['venue']); ?></td>
                                                <td><?php echo number_format($event['max_capacity']); ?></td>
                                                <td><?php echo formatPrice($event['base_price']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $event['status'] === 'published' ? 'success' : ($event['status'] === 'draft' ? 'warning' : 'secondary'); ?>">
                                                        <?php 
                                                        $statusLabels = [
                                                            'published' => 'Опубликовано',
                                                            'draft' => 'Черновик',
                                                            'cancelled' => 'Отменено',
                                                            'completed' => 'Завершено'
                                                        ];
                                                        echo $statusLabels[$event['status']] ?? $event['status'];
                                                        ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="?action=edit&id=<?php echo $event['id']; ?>" class="btn btn-outline-primary">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="../event-details.php?id=<?php echo $event['id']; ?>" class="btn btn-outline-info" target="_blank">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <button class="btn btn-outline-danger" onclick="deleteEvent(<?php echo $event['id']; ?>)">
                                                            <i class="fas fa-trash"></i>
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
    <?php endif; ?>
</div>

<script>
function deleteEvent(eventId) {
    if (confirm('Вы уверены, что хотите удалить это мероприятие?')) {
        // Здесь должна быть AJAX-запрос для удаления
        alert('Функция удаления будет реализована');
    }
}
</script>

<?php
$content = ob_get_clean();
$pageTitle = 'Управление мероприятиями';

include '../header.php';
?>
