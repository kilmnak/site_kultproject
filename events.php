<?php
require_once 'config.php';
require_once 'includes/functions.php';

session_start();

$auth = new Auth();
$eventManager = new EventManager();

// Получаем параметры фильтрации
$status = $_GET['status'] ?? 'published';
$search = $_GET['search'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 12;
$offset = ($page - 1) * $limit;

// Проверяем, является ли пользователь администратором
$isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;

// Если пользователь не администратор, скрываем черновики
if (!$isAdmin && $status === 'draft') {
    $status = 'published';
}

// Получаем мероприятия
$events = $eventManager->getEvents($status, $limit, $offset);

// Если есть поиск, фильтруем результаты
if ($search) {
    $events = array_filter($events, function($event) use ($search) {
        return stripos($event['title'], $search) !== false || 
               stripos($event['description'], $search) !== false ||
               stripos($event['venue'], $search) !== false;
    });
}

ob_start();
?>

<div class="container my-5">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">Мероприятия</h1>
        </div>
    </div>
    
    <!-- Фильтры и поиск -->
    <div class="row mb-4">
        <div class="col-md-8">
            <form method="GET" class="d-flex">
                <input type="text" name="search" class="form-control me-2" placeholder="Поиск мероприятий..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>
        <div class="col-md-4">
            <form method="GET" class="d-flex">
                <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="published" <?php echo $status === 'published' ? 'selected' : ''; ?>>Опубликованные</option>
                    <?php if ($isAdmin): ?>
                        <option value="draft" <?php echo $status === 'draft' ? 'selected' : ''; ?>>Черновики</option>
                    <?php endif; ?>
                    <option value="cancelled" <?php echo $status === 'cancelled' ? 'selected' : ''; ?>>Отмененные</option>
                    <option value="completed" <?php echo $status === 'completed' ? 'selected' : ''; ?>>Завершенные</option>
                </select>
            </form>
        </div>
    </div>
    
    <!-- Список мероприятий -->
    <div class="row">
        <?php if (empty($events)): ?>
            <div class="col-12">
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle me-2"></i>
                    Мероприятия не найдены.
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($events as $event): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100 shadow-sm">
                        <?php if ($event['image_url']): ?>
                            <img src="<?php echo $event['image_url']; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($event['title']); ?>" style="height: 200px; object-fit: cover;">
                        <?php else: ?>
                            <div class="card-img-top bg-secondary d-flex align-items-center justify-content-center" style="height: 200px;">
                                <i class="fas fa-calendar-alt text-white display-4"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title"><?php echo htmlspecialchars($event['title']); ?></h5>
                                <?php if ($isAdmin || $event['status'] !== 'draft'): ?>
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
                                <?php endif; ?>
                            </div>
                            
                            <p class="card-text text-muted small">
                                <i class="fas fa-calendar me-1"></i>
                                <?php echo formatDate($event['event_date'], 'd.m.Y'); ?>
                            </p>
                            <p class="card-text text-muted small">
                                <i class="fas fa-clock me-1"></i>
                                <?php echo formatDate($event['event_date'], 'H:i'); ?>
                            </p>
                            <p class="card-text text-muted small">
                                <i class="fas fa-map-marker-alt me-1"></i>
                                <?php echo htmlspecialchars($event['venue']); ?>
                            </p>
                            <p class="card-text"><?php echo htmlspecialchars(substr($event['description'], 0, 120)) . '...'; ?></p>
                            
                            <div class="mt-auto">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="h5 text-primary mb-0"><?php echo formatPrice($event['base_price']); ?></span>
                                    <a href="/event-details.php?id=<?php echo $event['id']; ?>" class="btn btn-primary btn-sm">
                                        Подробнее
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <!-- Пагинация -->
    <?php if (count($events) >= $limit): ?>
        <div class="row">
            <div class="col-12">
                <nav aria-label="Навигация по страницам">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>">Предыдущая</a>
                            </li>
                        <?php endif; ?>
                        
                        <li class="page-item active">
                            <span class="page-link"><?php echo $page; ?></span>
                        </li>
                        
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>">Следующая</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
$pageTitle = 'Мероприятия';

include 'header.php';
?>
