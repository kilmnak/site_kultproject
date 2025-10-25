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

// Получаем 3 ближайших мероприятия
$upcomingEvents = $eventManager->getUpcomingEvents(3);

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
            <h2 class="text-center mb-5">Ближайшие мероприятия</h2>
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
                                    <div class="d-flex gap-2">
                                        <a href="/event-details.php?id=<?php echo $event['id']; ?>" class="btn btn-outline-primary btn-sm">
                                            Подробнее
                                        </a>
                                        <button type="button" class="btn btn-success btn-sm" onclick="openBookingModal(<?php echo $event['id']; ?>, '<?php echo htmlspecialchars($event['title']); ?>', <?php echo $event['base_price']; ?>)">
                                            <i class="fas fa-shopping-cart me-1"></i>Купить
                                        </button>
                                    </div>
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

<!-- Модальное окно покупки билетов -->
<div class="modal fade" id="bookingModal" tabindex="-1" aria-labelledby="bookingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bookingModalLabel">Покупка билетов</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 id="eventTitle"></h6>
                        <p class="text-muted" id="eventPrice"></p>
                        
                        <div class="mb-3">
                            <label for="ticketQuantity" class="form-label">Количество билетов</label>
                            <select class="form-select" id="ticketQuantity" onchange="updateTotal()">
                                <option value="1">1 билет</option>
                                <option value="2">2 билета</option>
                                <option value="3">3 билета</option>
                                <option value="4">4 билета</option>
                                <option value="5">5 билетов</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Выберите схему рассадки</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="venueType" id="club" value="club" checked>
                                <label class="btn btn-outline-primary" for="club">Клуб</label>
                                
                                <input type="radio" class="btn-check" name="venueType" id="cinema" value="cinema">
                                <label class="btn btn-outline-primary" for="cinema">Кинотеатр</label>
                                
                                <input type="radio" class="btn-check" name="venueType" id="theater" value="theater">
                                <label class="btn btn-outline-primary" for="theater">Театр</label>
                            </div>
                        </div>
                        
                        <div class="alert alert-info">
                            <strong>Итого:</strong> <span id="totalPrice">0 ₽</span>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div id="venueLayout">
                            <!-- Схема рассадки будет загружена здесь -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-success" onclick="proceedToBooking()">
                    <i class="fas fa-shopping-cart me-1"></i>Перейти к оплате
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let currentEventId = null;
let currentEventPrice = 0;

function openBookingModal(eventId, eventTitle, eventPrice) {
    currentEventId = eventId;
    currentEventPrice = eventPrice;
    
    document.getElementById('eventTitle').textContent = eventTitle;
    document.getElementById('eventPrice').textContent = 'Цена за билет: ' + formatPrice(eventPrice);
    
    updateTotal();
    loadVenueLayout('club'); // По умолчанию клуб
    
    new bootstrap.Modal(document.getElementById('bookingModal')).show();
}

function updateTotal() {
    const quantity = parseInt(document.getElementById('ticketQuantity').value);
    const total = quantity * currentEventPrice;
    document.getElementById('totalPrice').textContent = formatPrice(total);
}

function loadVenueLayout(type) {
    const layoutDiv = document.getElementById('venueLayout');
    
    switch(type) {
        case 'club':
            layoutDiv.innerHTML = `
                <h6>Схема клуба</h6>
                <div class="venue-layout club-layout">
                    <div class="zone vip-zone">
                        <h6>VIP зона</h6>
                        <div class="seats-grid">
                            <div class="seat vip" data-price="${currentEventPrice * 2}">VIP1</div>
                            <div class="seat vip" data-price="${currentEventPrice * 2}">VIP2</div>
                            <div class="seat vip" data-price="${currentEventPrice * 2}">VIP3</div>
                            <div class="seat vip" data-price="${currentEventPrice * 2}">VIP4</div>
                        </div>
                    </div>
                    <div class="zone dance-floor">
                        <h6>Танцпол</h6>
                        <div class="seats-grid">
                            <div class="seat dance" data-price="${currentEventPrice}">D1</div>
                            <div class="seat dance" data-price="${currentEventPrice}">D2</div>
                            <div class="seat dance" data-price="${currentEventPrice}">D3</div>
                            <div class="seat dance" data-price="${currentEventPrice}">D4</div>
                            <div class="seat dance" data-price="${currentEventPrice}">D5</div>
                            <div class="seat dance" data-price="${currentEventPrice}">D6</div>
                        </div>
                    </div>
                    <div class="zone second-floor">
                        <h6>Второй этаж</h6>
                        <div class="seats-grid">
                            <div class="seat floor2" data-price="${currentEventPrice * 1.5}">F1</div>
                            <div class="seat floor2" data-price="${currentEventPrice * 1.5}">F2</div>
                            <div class="seat floor2" data-price="${currentEventPrice * 1.5}">F3</div>
                            <div class="seat floor2" data-price="${currentEventPrice * 1.5}">F4</div>
                        </div>
                    </div>
                </div>
            `;
            break;
            
        case 'cinema':
            layoutDiv.innerHTML = `
                <h6>Схема кинотеатра</h6>
                <div class="venue-layout cinema-layout">
                    <div class="screen">ЭКРАН</div>
                    <div class="seats-grid cinema-seats">
                        ${generateCinemaSeats()}
                    </div>
                </div>
            `;
            break;
            
        case 'theater':
            layoutDiv.innerHTML = `
                <h6>Схема театра</h6>
                <div class="venue-layout theater-layout">
                    <div class="stage">СЦЕНА</div>
                    <div class="seats-grid theater-seats">
                        ${generateTheaterSeats()}
                    </div>
                </div>
            `;
            break;
    }
    
    // Добавляем обработчики клика на места
    document.querySelectorAll('.seat').forEach(seat => {
        seat.addEventListener('click', function() {
            this.classList.toggle('selected');
        });
    });
}

function generateCinemaSeats() {
    let seats = '';
    for (let row = 1; row <= 5; row++) {
        for (let seat = 1; seat <= 6; seat++) {
            const seatNumber = String.fromCharCode(64 + row) + seat;
            seats += `<div class="seat cinema" data-price="${currentEventPrice}">${seatNumber}</div>`;
        }
    }
    return seats;
}

function generateTheaterSeats() {
    let seats = '';
    for (let row = 1; row <= 10; row++) {
        for (let seat = 1; seat <= 10; seat++) {
            const seatNumber = String.fromCharCode(64 + row) + seat;
            seats += `<div class="seat theater" data-price="${currentEventPrice}">${seatNumber}</div>`;
        }
    }
    return seats;
}

function proceedToBooking() {
    const quantity = parseInt(document.getElementById('ticketQuantity').value);
    const venueType = document.querySelector('input[name="venueType"]:checked').value;
    const selectedSeats = Array.from(document.querySelectorAll('.seat.selected')).map(seat => seat.textContent);
    
    if (selectedSeats.length !== quantity) {
        alert('Пожалуйста, выберите ' + quantity + ' мест');
        return;
    }
    
    // Перенаправляем на страницу бронирования
    const params = new URLSearchParams({
        event_id: currentEventId,
        quantity: quantity,
        venue_type: venueType,
        seats: selectedSeats.join(',')
    });
    
    window.location.href = 'booking.php?' + params.toString();
}

function formatPrice(price) {
    return new Intl.NumberFormat('ru-RU').format(price) + ' ₽';
}

// Обработчик изменения типа заведения
document.querySelectorAll('input[name="venueType"]').forEach(radio => {
    radio.addEventListener('change', function() {
        loadVenueLayout(this.value);
    });
});
</script>

<style>
.venue-layout {
    border: 2px solid #ddd;
    padding: 15px;
    border-radius: 8px;
    background: #f8f9fa;
}

.zone {
    margin-bottom: 15px;
}

.zone h6 {
    font-size: 0.9rem;
    margin-bottom: 8px;
    color: #495057;
}

.seats-grid {
    display: grid;
    gap: 5px;
    margin-bottom: 10px;
}

.club-layout .seats-grid {
    grid-template-columns: repeat(auto-fit, minmax(60px, 1fr));
}

.cinema-layout .seats-grid {
    grid-template-columns: repeat(6, 1fr);
}

.theater-layout .seats-grid {
    grid-template-columns: repeat(10, 1fr);
}

.seat {
    width: 40px;
    height: 40px;
    border: 2px solid #ddd;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.2s;
}

.seat:hover {
    border-color: #007bff;
    background-color: #e3f2fd;
}

.seat.selected {
    background-color: #28a745;
    color: white;
    border-color: #28a745;
}

.seat.vip {
    background-color: #ffd700;
    border-color: #ffd700;
}

.seat.dance {
    background-color: #17a2b8;
    color: white;
}

.seat.floor2 {
    background-color: #6f42c1;
    color: white;
}

.seat.cinema {
    background-color: #6c757d;
    color: white;
}

.seat.theater {
    background-color: #dc3545;
    color: white;
}

.screen, .stage {
    text-align: center;
    background: #343a40;
    color: white;
    padding: 10px;
    margin-bottom: 15px;
    border-radius: 4px;
    font-weight: bold;
}
</style>

<?php
$content = ob_get_clean();
$pageTitle = 'Главная';

include 'header.php';
?>
