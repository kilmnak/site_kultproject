<?php
// admin/events.php - Управление мероприятиями (CRUD)
require_once '../includes/venue_templates.php';

$db = Database::getInstance();
$eventManager = new EventManager();

$action = $_GET['action'] ?? 'list';
$eventId = intval($_GET['id'] ?? 0);
$message = '';
$messageType = '';

// Обработка POST запросов
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $data = [
            'title' => sanitizeInput($_POST['title']),
            'description' => sanitizeInput($_POST['description']),
            'event_date' => $_POST['event_date'],
            'venue' => sanitizeInput($_POST['venue']),
            'address' => sanitizeInput($_POST['address']),
            'max_capacity' => intval($_POST['max_capacity']),
            'base_price' => floatval($_POST['base_price']),
            'status' => $_POST['status'],
            'image_url' => sanitizeInput($_POST['image_url']),
            'venue_layout' => $_POST['venue_layout'] ?? 'none'
        ];

        if ($_POST['action'] === 'create') {
            $newId = $eventManager->createEvent($data);
            if ($newId) {
                // Создаем места по выбранному шаблону
                if ($data['venue_layout'] !== 'none') {
                    $seats = VenueTemplates::createLayoutByTemplate($data['venue_layout'], $newId, $data['base_price'], $db);
                    
                    // Создаем ценовые категории
                    $templates = VenueTemplates::getTemplates();
                    $template = $templates[$data['venue_layout']] ?? null;
                    
                    if ($template) {
                        foreach ($template['zones'] as $zoneKey => $zone) {
                            $db->query(
                                "INSERT INTO price_categories (event_id, name, price, description) VALUES (?, ?, ?, ?)",
                                [$newId, $zone['name'], $data['base_price'] * $zone['price_multiplier'], $zone['description']]
                            );
                            $categoryId = $db->lastInsertId();
                            
                            // Обновляем места с категорией
                            foreach ($seats as $seat) {
                                if ($seat['section'] === $zone['name']) {
                                    $db->query(
                                        "INSERT INTO seats (event_id, seat_number, row_number, section, price_category_id, status) VALUES (?, ?, ?, ?, ?, ?)",
                                        [$seat['event_id'], $seat['seat_number'], $seat['row_number'], $seat['section'], $categoryId, $seat['status']]
                                    );
                                }
                            }
                        }
                    }
                }
                
                $message = 'Мероприятие успешно создано!';
                $messageType = 'success';
                $action = 'list';
            } else {
                $message = 'Ошибка при создании мероприятия.';
                $messageType = 'danger';
            }
        } elseif ($_POST['action'] === 'update') {
            if ($eventManager->updateEvent($eventId, $data)) {
                $message = 'Мероприятие успешно обновлено!';
                $messageType = 'success';
                $action = 'list';
            } else {
                $message = 'Ошибка при обновлении мероприятия.';
                $messageType = 'danger';
            }
        }
    } elseif (isset($_POST['delete_id'])) {
        $deleteId = intval($_POST['delete_id']);
        if ($eventManager->deleteEvent($deleteId)) {
            $message = 'Мероприятие успешно удалено!';
            $messageType = 'success';
        } else {
            $message = 'Ошибка при удалении мероприятия.';
            $messageType = 'danger';
        }
        $action = 'list';
    }
}

// Получаем данные для отображения
$events = $eventManager->getEvents();
$eventToEdit = null;
if ($action === 'edit' && $eventId > 0) {
    $eventToEdit = $eventManager->getEvent($eventId);
    if (!$eventToEdit) {
        $message = 'Мероприятие не найдено.';
        $messageType = 'danger';
        $action = 'list';
    }
}

// Статистика мероприятий
$eventStats = [
    'total' => $db->fetch("SELECT COUNT(*) as count FROM events")['count'],
    'published' => $db->fetch("SELECT COUNT(*) as count FROM events WHERE status = 'published'")['count'],
    'draft' => $db->fetch("SELECT COUNT(*) as count FROM events WHERE status = 'draft'")['count'],
    'cancelled' => $db->fetch("SELECT COUNT(*) as count FROM events WHERE status = 'cancelled'")['count'],
];

ob_start();
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800">Управление мероприятиями</h1>
            <a href="?page=events&action=create" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Создать мероприятие
            </a>
        </div>
    </div>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
        <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
        <?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($action === 'list'): ?>
    <!-- Статистика -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stats-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Всего мероприятий
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $eventStats['total']; ?></div>
                        </div>
                        <div class="col-auto">
                            <div class="stats-icon bg-primary">
                                <i class="fas fa-calendar-alt"></i>
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
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Опубликованные
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $eventStats['published']; ?></div>
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

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stats-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Черновики
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $eventStats['draft']; ?></div>
                        </div>
                        <div class="col-auto">
                            <div class="stats-icon bg-warning">
                                <i class="fas fa-edit"></i>
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
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Отмененные
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $eventStats['cancelled']; ?></div>
                        </div>
                        <div class="col-auto">
                            <div class="stats-icon bg-danger">
                                <i class="fas fa-times-circle"></i>
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
                <div class="col-md-6">
                    <div class="btn-group" role="group">
                        <a href="?page=events&action=list&filter=all" class="btn btn-outline-primary active">Все</a>
                        <a href="?page=events&action=list&filter=published" class="btn btn-outline-success">Опубликованные</a>
                        <a href="?page=events&action=list&filter=draft" class="btn btn-outline-warning">Черновики</a>
                        <a href="?page=events&action=list&filter=cancelled" class="btn btn-outline-danger">Отмененные</a>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Поиск мероприятий..." id="searchInput">
                        <button class="btn btn-outline-secondary" type="button">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Таблица мероприятий -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
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
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="fas fa-calendar-alt fa-3x mb-3"></i>
                                    <p>Мероприятий пока нет</p>
                                    <a href="?page=events&action=create" class="btn btn-primary">Создать первое мероприятие</a>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($events as $event): ?>
                                <tr>
                                    <td>
                                        <div>
                                            <strong><?php echo htmlspecialchars($event['title']); ?></strong>
                                            <?php if ($event['image_url']): ?>
                                                <br><small class="text-muted">Есть изображение</small>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td><?php echo formatDate($event['event_date']); ?></td>
                                    <td><?php echo htmlspecialchars($event['venue']); ?></td>
                                    <td><?php echo number_format($event['max_capacity']); ?></td>
                                    <td><?php echo formatPrice($event['base_price']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo match($event['status']) {
                                                'published' => 'success',
                                                'draft' => 'warning',
                                                'cancelled' => 'danger',
                                                'completed' => 'info',
                                                default => 'secondary'
                                            };
                                        ?>">
                                            <?php echo match($event['status']) {
                                                'published' => 'Опубликовано',
                                                'draft' => 'Черновик',
                                                'cancelled' => 'Отменено',
                                                'completed' => 'Завершено',
                                                default => $event['status']
                                            }; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="?page=events&action=edit&id=<?php echo $event['id']; ?>" class="btn btn-outline-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-danger" onclick="confirmDelete(<?php echo $event['id']; ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
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

<?php elseif ($action === 'create' || $action === 'edit'): ?>
    <!-- Форма создания/редактирования -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-<?php echo $action === 'create' ? 'plus' : 'edit'; ?> me-2"></i>
                        <?php echo $action === 'create' ? 'Создание мероприятия' : 'Редактирование мероприятия'; ?>
                    </h6>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="<?php echo $action; ?>">
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Название мероприятия <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="title" name="title" 
                                           value="<?php echo htmlspecialchars($eventToEdit['title'] ?? ''); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Статус <span class="text-danger">*</span></label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="draft" <?php echo ($eventToEdit['status'] ?? '') === 'draft' ? 'selected' : ''; ?>>Черновик</option>
                                        <option value="published" <?php echo ($eventToEdit['status'] ?? '') === 'published' ? 'selected' : ''; ?>>Опубликовано</option>
                                        <option value="cancelled" <?php echo ($eventToEdit['status'] ?? '') === 'cancelled' ? 'selected' : ''; ?>>Отменено</option>
                                        <option value="completed" <?php echo ($eventToEdit['status'] ?? '') === 'completed' ? 'selected' : ''; ?>>Завершено</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Описание</label>
                            <textarea class="form-control" id="description" name="description" rows="4"><?php echo htmlspecialchars($eventToEdit['description'] ?? ''); ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="event_date" class="form-label">Дата и время <span class="text-danger">*</span></label>
                                    <input type="datetime-local" class="form-control" id="event_date" name="event_date" 
                                           value="<?php echo $eventToEdit ? date('Y-m-d\TH:i', strtotime($eventToEdit['event_date'])) : ''; ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="venue" class="form-label">Место проведения <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="venue" name="venue" 
                                           value="<?php echo htmlspecialchars($eventToEdit['venue'] ?? ''); ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Адрес</label>
                            <input type="text" class="form-control" id="address" name="address" 
                                   value="<?php echo htmlspecialchars($eventToEdit['address'] ?? ''); ?>">
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="max_capacity" class="form-label">Максимальная вместимость <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="max_capacity" name="max_capacity" 
                                           value="<?php echo $eventToEdit['max_capacity'] ?? ''; ?>" min="1" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="base_price" class="form-label">Базовая цена (₽) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="base_price" name="base_price" 
                                           value="<?php echo $eventToEdit['base_price'] ?? ''; ?>" min="0" step="0.01" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="image_url" class="form-label">URL изображения</label>
                                    <input type="url" class="form-control" id="image_url" name="image_url" 
                                           value="<?php echo htmlspecialchars($eventToEdit['image_url'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Схема рассадки -->
                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="venue_layout" class="form-label">Схема рассадки</label>
                                    <select class="form-select" id="venue_layout" name="venue_layout">
                                        <option value="none" <?php echo ($eventToEdit['venue_layout'] ?? 'none') === 'none' ? 'selected' : ''; ?>>Без схемы рассадки</option>
                                        <?php 
                                        $templates = VenueTemplates::getTemplates();
                                        foreach ($templates as $key => $template): 
                                        ?>
                                            <option value="<?php echo $key; ?>" <?php echo ($eventToEdit['venue_layout'] ?? '') === $key ? 'selected' : ''; ?>>
                                                <?php echo $template['name']; ?> - <?php echo $template['description']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Выберите схему рассадки для автоматического создания мест и ценовых категорий
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Предварительный просмотр схемы -->
                        <div id="layoutPreview" class="mb-3" style="display: none;">
                            <h6>Предварительный просмотр схемы:</h6>
                            <div class="border rounded p-3 bg-light" id="previewContent">
                                <!-- Здесь будет отображаться схема -->
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="?page=events&action=list" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Назад к списку
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>
                                <?php echo $action === 'create' ? 'Создать мероприятие' : 'Сохранить изменения'; ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="fas fa-info-circle me-2"></i>Справка
                    </h6>
                </div>
                <div class="card-body">
                    <h6>Статусы мероприятий:</h6>
                    <ul class="list-unstyled">
                        <li><span class="badge bg-warning me-2">Черновик</span> - не отображается на сайте</li>
                        <li><span class="badge bg-success me-2">Опубликовано</span> - доступно для бронирования</li>
                        <li><span class="badge bg-danger me-2">Отменено</span> - мероприятие отменено</li>
                        <li><span class="badge bg-info me-2">Завершено</span> - мероприятие прошло</li>
                    </ul>
                    
                    <hr>
                    
                    <h6>Рекомендации:</h6>
                    <ul class="small">
                        <li>Используйте описательные названия</li>
                        <li>Указывайте точный адрес</li>
                        <li>Проверяйте дату и время</li>
                        <li>Добавляйте изображения для привлекательности</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Модальное окно подтверждения удаления -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Подтверждение удаления</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Вы уверены, что хотите удалить это мероприятие?</p>
                <p class="text-danger"><strong>Это действие нельзя отменить!</strong></p>
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
function confirmDelete(eventId) {
    document.getElementById('deleteId').value = eventId;
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const venueLayoutSelect = document.getElementById('venue_layout');
    const layoutPreview = document.getElementById('layoutPreview');
    const previewContent = document.getElementById('previewContent');
    
    if (venueLayoutSelect) {
        venueLayoutSelect.addEventListener('change', function() {
            const selectedLayout = this.value;
            
            if (selectedLayout === 'none') {
                layoutPreview.style.display = 'none';
            } else {
                layoutPreview.style.display = 'block';
                showLayoutPreview(selectedLayout);
            }
        });
        
        // Показываем превью при загрузке страницы, если уже выбрана схема
        if (venueLayoutSelect.value !== 'none') {
            layoutPreview.style.display = 'block';
            showLayoutPreview(venueLayoutSelect.value);
        }
    }
    
    function showLayoutPreview(layoutType) {
        const templates = {
            'club': {
                name: 'Клуб',
                zones: [
                    { name: 'VIP зона', capacity: 50, color: '#ffd700' },
                    { name: 'Танцпол', capacity: 200, color: '#17a2b8' },
                    { name: 'Второй этаж', capacity: 100, color: '#6f42c1' }
                ]
            },
            'cinema': {
                name: 'Кинотеатр',
                zones: [
                    { name: 'Партер', capacity: 150, color: '#6c757d' },
                    { name: 'Балкон', capacity: 50, color: '#6c757d' }
                ]
            },
            'theater': {
                name: 'Театр',
                zones: [
                    { name: 'Партер', capacity: 200, color: '#dc3545' },
                    { name: 'Бельэтаж', capacity: 100, color: '#dc3545' },
                    { name: 'Балкон', capacity: 80, color: '#dc3545' }
                ]
            }
        };
        
        const template = templates[layoutType];
        if (!template) return;
        
        let html = `<h6>${template.name}</h6>`;
        
        if (layoutType === 'club') {
            html += `
                <div class="club-preview">
                    <div class="stage-preview mb-3">
                        <div class="stage">СЦЕНА</div>
                    </div>
                    <div class="zones-preview">
                        <div class="zone vip-zone mb-2">
                            <span class="badge" style="background-color: #ffd700; color: #000;">VIP зона (50 мест)</span>
                        </div>
                        <div class="zone dance-zone mb-2">
                            <span class="badge" style="background-color: #17a2b8;">Танцпол (200 мест)</span>
                        </div>
                        <div class="zone second-floor-zone">
                            <span class="badge" style="background-color: #6f42c1;">Второй этаж (100 мест)</span>
                        </div>
                    </div>
                </div>
            `;
        } else {
            html += '<div class="zones-preview">';
            template.zones.forEach(zone => {
                html += `<div class="zone mb-2">
                    <span class="badge" style="background-color: ${zone.color};">
                        ${zone.name} (${zone.capacity} мест)
                    </span>
                </div>`;
            });
            html += '</div>';
        }
        
        previewContent.innerHTML = html;
    }
});
</script>

<style>
.club-preview .stage {
    background: #343a40;
    color: white;
    padding: 10px;
    text-align: center;
    border-radius: 4px;
    font-weight: bold;
}

.zones-preview .zone {
    padding: 5px 0;
}

.badge {
    font-size: 0.9rem;
    padding: 8px 12px;
}
</style>

<?php
$content = ob_get_clean();
$pageTitle = 'Управление мероприятиями';
include 'admin-header.php';
?>
