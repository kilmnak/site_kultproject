<?php
require_once 'config.php';
require_once 'includes/functions.php';

session_start();

$auth = new Auth();
$eventManager = new EventManager();

// Обработка сообщений
$message = '';
$messageType = '';

if (isset($_GET['logout']) && $_GET['logout'] == '1') {
    $message = 'Вы успешно вышли из системы.';
    $messageType = 'success';
}

// Получаем предстоящие мероприятия
$upcomingEvents = $eventManager->getUpcomingEvents(6);

ob_start();
?>

<?php if ($message): ?>
<div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show mt-3 mx-auto" role="alert" style="max-width: 90%;">
    <?php echo $message; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="hero-section bg-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-4">Добро пожаловать в <?php echo SITE_NAME; ?></h1>
                <p class="lead mb-4">Откройте для себя мир культуры и искусства. Билеты на лучшие концерты, театральные постановки и фестивали в одном месте.</p>
                <a href="/events.php" class="btn btn-light btn-lg">Посмотреть мероприятия</a>
            </div>
            <div class="col-lg-6">
                <div class="hero-image text-center">
                    <i class="fas fa-theater-masks display-1 opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container my-5">
    <div class="row">
        <div class="col-12">
            <h2 class="text-center mb-5">Предстоящие мероприятия</h2>
        </div>
    </div>
    
    <div class="row">
        <?php if (empty($upcomingEvents)): ?>
            <div class="col-12">
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle me-2"></i>
                    В данный момент нет доступных мероприятий. Следите за обновлениями!
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($upcomingEvents as $event): ?>
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
                            <h5 class="card-title"><?php echo htmlspecialchars($event['title']); ?></h5>
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
                            <p class="card-text"><?php echo htmlspecialchars(substr($event['description'], 0, 100)) . '...'; ?></p>
                            
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
    
    <div class="row mt-5">
        <div class="col-12 text-center">
            <a href="/events.php" class="btn btn-outline-primary btn-lg">Все мероприятия</a>
        </div>
    </div>
</div>

<div class="bg-light py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 text-center mb-4">
                <div class="feature-icon mb-3">
                    <i class="fas fa-ticket-alt text-primary display-4"></i>
                </div>
                <h4>Удобная покупка билетов</h4>
                <p class="text-muted">Быстро и безопасно покупайте билеты онлайн с возможностью выбора мест на интерактивной схеме зала.</p>
            </div>
            <div class="col-lg-4 text-center mb-4">
                <div class="feature-icon mb-3">
                    <i class="fas fa-mobile-alt text-primary display-4"></i>
                </div>
                <h4>Мобильные билеты</h4>
                <p class="text-muted">Электронные билеты с QR-кодами прямо на вашем смартфоне. Никаких бумажных билетов!</p>
            </div>
            <div class="col-lg-4 text-center mb-4">
                <div class="feature-icon mb-3">
                    <i class="fas fa-headset text-primary display-4"></i>
                </div>
                <h4>Поддержка 24/7</h4>
                <p class="text-muted">Наша служба поддержки всегда готова помочь вам с любыми вопросами по билетам и мероприятиям.</p>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = 'Главная';

include 'header.php';
?>
