<?php
// Получаем параметры фильтрации
$category = $_GET['category'] ?? '';
$search = $_GET['search'] ?? '';

// Формируем SQL запрос
$sql = "SELECT * FROM events WHERE event_date >= CURDATE()";
$params = [];

if(!empty($category)) {
    $sql .= " AND category = ?";
    $params[] = $category;
}

if(!empty($search)) {
    $sql .= " AND (title LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " ORDER BY event_date";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<section class="events-section">
    <div class="container">
        <h2 class="section-title">Все мероприятия</h2>
        
        <div class="filters">
            <a href="index.php?module=events&action=list" class="filter-btn <?php echo empty($category) ? 'active' : ''; ?>">Все</a>
            <a href="index.php?module=events&action=list&category=concerts" class="filter-btn <?php echo $category == 'concerts' ? 'active' : ''; ?>">Концерты</a>
            <a href="index.php?module=events&action=list&category=theater" class="filter-btn <?php echo $category == 'theater' ? 'active' : ''; ?>">Театр</a>
            <a href="index.php?module=events&action=list&category=festivals" class="filter-btn <?php echo $category == 'festivals' ? 'active' : ''; ?>">Фестивали</a>
            <a href="index.php?module=events&action=list&category=exhibitions" class="filter-btn <?php echo $category == 'exhibitions' ? 'active' : ''; ?>">Выставки</a>
        </div>
        
        <?php if(!empty($search)): ?>
            <div class="search-results">
                <p>Результаты поиска для: "<?php echo htmlspecialchars($search); ?>"</p>
            </div>
        <?php endif; ?>
        
        <div class="events-grid">
            <?php if(count($events) > 0): ?>
                <?php foreach($events as $event): ?>
                <div class="event-card">
                    <div class="event-image" style="background-image: url('assets/images/events/<?php echo $event['image']; ?>');"></div>
                    <div class="event-info">
                        <div class="event-date"><?php echo date('d.m.Y, H:i', strtotime($event['event_date'])); ?></div>
                        <h3 class="event-title"><?php echo htmlspecialchars($event['title']); ?></h3>
                        <div class="event-location"><?php echo htmlspecialchars($event['location']); ?></div>
                        <div class="event-price">от <?php echo $event['min_price']; ?> руб.</div>
                        <a href="index.php?module=tickets&action=purchase&event_id=<?php echo $event['id']; ?>" class="buy-btn">Купить билет</a>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-events">
                    <p>Мероприятия не найдены</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>