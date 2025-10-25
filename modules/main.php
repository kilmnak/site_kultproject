<!-- Герой-секция -->
<section class="hero">
    <div class="container">
        <h1>Лучшие культурные мероприятия в вашем городе</h1>
        <p>Концерты, театральные премьеры, фестивали и выставки от ведущих организаторов</p>
        <form method="GET" action="index.php" class="search-box">
            <input type="hidden" name="module" value="events">
            <input type="hidden" name="action" value="list">
            <input type="text" name="search" placeholder="Поиск мероприятий...">
            <button type="submit">Найти</button>
        </form>
    </div>
</section>

<!-- Секция мероприятий -->
<section class="events-section" id="events">
    <div class="container">
        <h2 class="section-title">Ближайшие мероприятия</h2>
        
        <?php
        // Получаем последние мероприятия из БД
        $stmt = $pdo->query("SELECT * FROM events WHERE event_date >= CURDATE() ORDER BY event_date LIMIT 4");
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>
        
        <div class="events-grid">
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
        </div>
    </div>
</section>

<!-- Секция о компании -->
<section class="about-section" id="about">
    <div class="container">
        <h2 class="section-title">О нашем агентстве</h2>
        <div class="about-content">
            <div class="about-text">
                <p>Концертное агентство "<?php echo SITE_NAME; ?>" занимается организацией и проведением культурно-массовых мероприятий с 2010 года. Мы предлагаем широкий спектр услуг:</p>
                <ul>
                    <li>Организация концертов, театральных премьер, фестивалей и выставок</li>
                    <li>Продажа билетов через онлайн-каналы и партнерские точки продаж</li>
                    <li>Полный цикл сопровождения мероприятий</li>
                    <li>Работа с ведущими артистами и организаторами</li>
                </ul>
                <p>Наша миссия - делать культуру доступной для каждого!</p>
            </div>
            <div class="about-image"></div>
        </div>
    </div>
</section>