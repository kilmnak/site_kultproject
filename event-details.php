<?php
require_once 'config.php';
require_once 'includes/functions.php';
require_once 'includes/venue_templates.php';

session_start();

$auth = new Auth();
$eventManager = new EventManager();
$bookingManager = new BookingManager();

$eventId = intval($_GET['id'] ?? 0);
$event = $eventManager->getEvent($eventId);

if (!$event) {
    showMessage('Мероприятие не найдено', 'error');
    redirect('/events.php');
}

// Обработка покупки билетов
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'book_tickets') {
    if (!$auth->isLoggedIn()) {
        showMessage('Для покупки билетов необходимо войти в систему', 'error');
        redirect('/login.php');
    }
    
    $seatIds = $_POST['seat_ids'] ?? [];
    $ticketQuantity = intval($_POST['ticket_quantity'] ?? 0);
    
    if (empty($seatIds)) {
        showMessage('Выберите места для покупки', 'error');
    } elseif ($ticketQuantity <= 0) {
        showMessage('Выберите количество билетов', 'error');
    } elseif (count($seatIds) !== $ticketQuantity) {
        showMessage('Количество выбранных мест должно соответствовать количеству билетов', 'error');
    } else {
        try {
            // Проверяем доступность мест
            $availableSeats = $db->fetchAll(
                "SELECT id FROM seats WHERE id IN (" . implode(',', array_map('intval', $seatIds)) . ") AND status = 'available' AND event_id = ?",
                [$eventId]
            );
            
            if (count($availableSeats) !== count($seatIds)) {
                showMessage('Некоторые выбранные места больше не доступны. Обновите страницу и выберите другие места.', 'error');
            } else {
                $bookingId = $bookingManager->createBooking($_SESSION['user_id'], $eventId, $seatIds);
                showMessage('Места успешно забронированы! Перейдите к оплате.', 'success');
                redirect('/booking.php?id=' . $bookingId);
            }
        } catch (Exception $e) {
            showMessage('Ошибка при бронировании: ' . $e->getMessage(), 'error');
        }
    }
}

// Получаем ценовые категории
$db = Database::getInstance();
$priceCategories = $db->fetchAll(
    "SELECT * FROM price_categories WHERE event_id = ? ORDER BY price ASC",
    [$eventId]
);

// Получаем схему зала (для будущего использования)
// $venueLayout = $db->fetch(
//     "SELECT layout_data FROM venue_layouts WHERE event_id = ?",
//     [$eventId]
// );

// Получаем места
$seats = $db->fetchAll(
    "SELECT s.*, pc.name as category_name, pc.price 
     FROM seats s 
     LEFT JOIN price_categories pc ON s.price_category_id = pc.id 
     WHERE s.event_id = ? 
     ORDER BY s.`row_number`, s.seat_number",
    [$eventId]
);

ob_start();
?>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-8">
            <!-- Информация о мероприятии -->
            <div class="card mb-4">
                <?php if ($event['image_url']): ?>
                    <img src="<?php echo $event['image_url']; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($event['title']); ?>" style="height: 400px; object-fit: cover;">
                <?php endif; ?>
                
                <div class="card-body">
                    <h1 class="card-title"><?php echo htmlspecialchars($event['title']); ?></h1>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p class="text-muted">
                                <i class="fas fa-calendar me-2"></i>
                                <?php echo formatDate($event['event_date'], 'd.m.Y'); ?>
                            </p>
                            <p class="text-muted">
                                <i class="fas fa-clock me-2"></i>
                                <?php echo formatDate($event['event_date'], 'H:i'); ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="text-muted">
                                <i class="fas fa-map-marker-alt me-2"></i>
                                <?php echo htmlspecialchars($event['venue']); ?>
                            </p>
                            <p class="text-muted">
                                <i class="fas fa-users me-2"></i>
                                Вместимость: <?php echo number_format($event['max_capacity']); ?> мест
                            </p>
                        </div>
                    </div>
                    
                    <?php if ($event['address']): ?>
                        <p class="text-muted">
                            <i class="fas fa-location-arrow me-2"></i>
                            <?php echo htmlspecialchars($event['address']); ?>
                        </p>
                    <?php endif; ?>
                    
                    <div class="mt-3">
                        <h5>Описание</h5>
                        <p><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Схема зала -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Выбор мест</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($seats)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Схема зала пока не загружена. Обратитесь к администратору для настройки рассадки.
                        </div>
                    <?php else: ?>
                        <form id="bookingForm" method="POST">
                            <input type="hidden" name="action" value="book_tickets">
                            
                            <!-- Выбор количества билетов -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label for="ticketQuantity" class="form-label">
                                        <i class="fas fa-ticket-alt me-1"></i>Количество билетов
                                    </label>
                                    <select class="form-select" id="ticketQuantity" name="ticket_quantity" required>
                                        <option value="">Выберите количество</option>
                                        <option value="1">1 билет</option>
                                        <option value="2">2 билета</option>
                                        <option value="3">3 билета</option>
                                        <option value="4">4 билета</option>
                                        <option value="5">5 билетов</option>
                                        <option value="6">6 билетов</option>
                                        <option value="7">7 билетов</option>
                                        <option value="8">8 билетов</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Информация</label>
                                    <div class="alert alert-light mb-0">
                                        <small class="text-muted">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Выберите количество билетов, затем места на схеме
                                        </small>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Легенда -->
                            <div class="row mb-3">
                                <div class="col-12">
                                    <h6>Легенда:</h6>
                                    <div class="d-flex flex-wrap gap-2">
                                        <span class="badge bg-success">Доступно</span>
                                        <span class="badge bg-warning">Забронировано</span>
                                        <span class="badge bg-danger">Продано</span>
                                        <span class="badge bg-secondary">Заблокировано</span>
                                        <span class="badge bg-primary">Выбрано</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Схема мест -->
                            <div class="venue-layout mb-4" id="venueLayout">
                                <?php if ($event['venue_layout'] !== 'none' && !empty($seats)): ?>
                                    <!-- Используем шаблон рассадки -->
                                    <?php echo VenueTemplates::getLayoutHTML($event['venue_layout'], $seats); ?>
                                <?php else: ?>
                                    <!-- Стандартная схема по рядам -->
                                    <?php
                                    // Группируем места по рядам
                                    $seatsByRow = [];
                                    foreach ($seats as $seat) {
                                        $row = $seat['row_number'] ?? 'Без ряда';
                                        $seatsByRow[$row][] = $seat;
                                    }
                                    
                                    foreach ($seatsByRow as $rowNumber => $rowSeats):
                                    ?>
                                        <div class="row mb-3">
                                            <div class="col-12">
                                                <div class="d-flex align-items-center mb-2">
                                                    <h6 class="mb-0 me-3">Ряд <?php echo htmlspecialchars($rowNumber); ?></h6>
                                                    <?php if (!empty($rowSeats[0]['section'])): ?>
                                                        <span class="badge bg-info"><?php echo htmlspecialchars($rowSeats[0]['section']); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="seats-row d-flex flex-wrap gap-1">
                                                    <?php foreach ($rowSeats as $seat): ?>
                                                        <?php
                                                        $statusClass = [
                                                            'available' => 'seat-available',
                                                            'booked' => 'seat-booked',
                                                            'sold' => 'seat-sold',
                                                            'blocked' => 'seat-blocked'
                                                        ][$seat['status']] ?? 'seat-blocked';
                                                        
                                                        $disabled = $seat['status'] !== 'available' ? 'disabled' : '';
                                                        $price = $seat['price'] ?? $event['base_price'];
                                                        ?>
                                                        <label class="seat-btn <?php echo $statusClass; ?>" data-price="<?php echo $price; ?>" data-category="<?php echo htmlspecialchars($seat['category_name'] ?? 'Стандарт'); ?>">
                                                            <input type="checkbox" name="seat_ids[]" value="<?php echo $seat['id']; ?>" <?php echo $disabled; ?> class="seat-checkbox d-none">
                                                            <span class="seat-number"><?php echo htmlspecialchars($seat['seat_number']); ?></span>
                                                            <small class="seat-price d-block"><?php echo formatPrice($price); ?></small>
                                                        </label>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Выбранные места -->
                            <div id="selectedSeats" class="mb-4" style="display: none;">
                                <div class="card border-primary">
                                    <div class="card-header bg-primary text-white">
                                        <h6 class="mb-0">
                                            <i class="fas fa-shopping-cart me-2"></i>Выбранные места
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div id="selectedSeatsList" class="mb-3"></div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div id="totalPrice" class="h5 text-primary mb-0"></div>
                                            </div>
                                            <div class="col-md-6 text-end">
                                                <small class="text-muted">
                                                    <i class="fas fa-info-circle me-1"></i>
                                                    Цены указаны за билет
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Кнопки действий -->
                            <div class="d-flex gap-3">
                                <button type="submit" class="btn btn-success btn-lg flex-grow-1" id="bookButton" disabled>
                                    <i class="fas fa-credit-card me-2"></i>
                                    Перейти к оплате
                                </button>
                                <button type="button" class="btn btn-outline-secondary" id="clearSelection" style="display: none;">
                                    <i class="fas fa-times me-1"></i>
                                    Очистить выбор
                                </button>
                            </div>
                            
                            <!-- Информация о бронировании -->
                            <div class="alert alert-info mt-3">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Важно:</strong> После выбора мест у вас будет 15 минут для завершения оплаты. 
                                Если оплата не будет произведена в течение этого времени, места станут доступными для других покупателей.
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- Ценовые категории -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Ценовые категории</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($priceCategories)): ?>
                        <p class="text-muted">Ценовые категории не настроены.</p>
                    <?php else: ?>
                        <?php foreach ($priceCategories as $category): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <strong><?php echo htmlspecialchars($category['name']); ?></strong>
                                    <?php if ($category['description']): ?>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($category['description']); ?></small>
                                    <?php endif; ?>
                                </div>
                                <span class="h6 text-primary mb-0"><?php echo formatPrice($category['price']); ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Информация о мероприятии -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Информация</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Дата:</strong><br>
                        <?php echo formatDate($event['event_date'], 'd.m.Y H:i'); ?>
                    </div>
                    <div class="mb-3">
                        <strong>Место:</strong><br>
                        <?php echo htmlspecialchars($event['venue']); ?>
                    </div>
                    <div class="mb-3">
                        <strong>Вместимость:</strong><br>
                        <?php echo number_format($event['max_capacity']); ?> мест
                    </div>
                    <div class="mb-3">
                        <strong>Статус:</strong><br>
                        <span class="badge bg-<?php echo $event['status'] === 'published' ? 'success' : 'warning'; ?>">
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
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('input[name="seat_ids[]"]');
    const selectedSeatsDiv = document.getElementById('selectedSeats');
    const selectedSeatsList = document.getElementById('selectedSeatsList');
    const totalPriceDiv = document.getElementById('totalPrice');
    const bookButton = document.getElementById('bookButton');
    const clearButton = document.getElementById('clearSelection');
    const ticketQuantitySelect = document.getElementById('ticketQuantity');
    
    let maxSeats = 0;
    let selectedSeats = [];
    
    // Обработчик изменения количества билетов
    ticketQuantitySelect.addEventListener('change', function() {
        maxSeats = parseInt(this.value) || 0;
        clearAllSelections();
        updateUI();
    });
    
    // Обработчики для мест
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            if (this.checked) {
                if (selectedSeats.length >= maxSeats) {
                    this.checked = false;
                    alert(`Максимальное количество мест: ${maxSeats}`);
                    return;
                }
                selectedSeats.push(this.value);
            } else {
                selectedSeats = selectedSeats.filter(id => id !== this.value);
            }
            updateSeatVisuals();
            updateUI();
        });
    });
    
    // Обработчик кнопки очистки
    clearButton.addEventListener('click', clearAllSelections);
    
    // Обработчик отправки формы
    document.getElementById('bookingForm').addEventListener('submit', function(e) {
        if (!ticketQuantitySelect.value) {
            e.preventDefault();
            alert('Выберите количество билетов');
            return;
        }
        
        if (selectedSeats.length !== maxSeats) {
            e.preventDefault();
            alert(`Выберите ровно ${maxSeats} мест`);
            return;
        }
        
        if (!confirm(`Подтвердите покупку ${maxSeats} билетов на сумму ${calculateTotalPrice()} ₽?`)) {
            e.preventDefault();
        }
    });
    
    function clearAllSelections() {
        selectedSeats = [];
        checkboxes.forEach(cb => cb.checked = false);
        updateSeatVisuals();
        updateUI();
    }
    
    function updateSeatVisuals() {
        document.querySelectorAll('.seat-btn').forEach(btn => {
            btn.classList.remove('seat-selected');
            const checkbox = btn.querySelector('.seat-checkbox');
            if (checkbox && selectedSeats.includes(checkbox.value)) {
                btn.classList.add('seat-selected');
            }
        });
    }
    
    function updateUI() {
        if (selectedSeats.length > 0) {
            selectedSeatsDiv.style.display = 'block';
            clearButton.style.display = 'block';
            
            // Обновляем список выбранных мест
            const seatDetails = selectedSeats.map(seatId => {
                const seatBtn = document.querySelector(`input[value="${seatId}"]`).closest('.seat-btn');
                const seatNumber = seatBtn.querySelector('.seat-number').textContent;
                const price = seatBtn.dataset.price;
                const category = seatBtn.dataset.category;
                return `${seatNumber} (${category}) - ${parseFloat(price).toLocaleString()} ₽`;
            });
            
            selectedSeatsList.innerHTML = seatDetails.join('<br>');
            totalPriceDiv.innerHTML = `<strong>Общая стоимость: ${calculateTotalPrice()} ₽</strong>`;
            
            bookButton.disabled = selectedSeats.length !== maxSeats;
            bookButton.textContent = selectedSeats.length === maxSeats ? 
                `Перейти к оплате (${calculateTotalPrice()} ₽)` : 
                `Выберите еще ${maxSeats - selectedSeats.length} мест`;
        } else {
            selectedSeatsDiv.style.display = 'none';
            clearButton.style.display = 'none';
            bookButton.disabled = true;
            bookButton.textContent = 'Перейти к оплате';
        }
    }
    
    function calculateTotalPrice() {
        return selectedSeats.reduce((total, seatId) => {
            const seatBtn = document.querySelector(`input[value="${seatId}"]`).closest('.seat-btn');
            return total + parseFloat(seatBtn.dataset.price || 0);
        }, 0).toLocaleString();
    }
});
</script>

<style>
.seat-btn {
    width: 60px;
    height: 60px;
    border: 2px solid #ddd;
    border-radius: 8px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    margin: 2px;
    font-size: 0.8rem;
    text-align: center;
}

.seat-btn:hover {
    transform: scale(1.05);
}

.seat-btn.seat-available {
    background-color: #d4edda;
    border-color: #28a745;
    color: #155724;
}

.seat-btn.seat-available:hover {
    background-color: #c3e6cb;
}

.seat-btn.seat-booked {
    background-color: #fff3cd;
    border-color: #ffc107;
    color: #856404;
    cursor: not-allowed;
}

.seat-btn.seat-sold {
    background-color: #f8d7da;
    border-color: #dc3545;
    color: #721c24;
    cursor: not-allowed;
}

.seat-btn.seat-blocked {
    background-color: #e2e3e5;
    border-color: #6c757d;
    color: #495057;
    cursor: not-allowed;
}

.seat-btn.seat-selected {
    background-color: #007bff;
    border-color: #0056b3;
    color: white;
    transform: scale(1.1);
}

.seat-number {
    font-weight: bold;
    font-size: 0.9rem;
}

.seat-price {
    font-size: 0.7rem;
    opacity: 0.8;
}

.seats-row {
    min-height: 70px;
    align-items: center;
}

.venue-layout {
    background-color: #f8f9fa;
    border-radius: 10px;
    padding: 20px;
    border: 2px solid #dee2e6;
}

@media (max-width: 768px) {
    .seat-btn {
        width: 50px;
        height: 50px;
        font-size: 0.7rem;
    }
    
    .seat-price {
        font-size: 0.6rem;
    }
}

/* Стили для схемы клуба */
.club-layout {
    background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
    border-radius: 15px;
    padding: 20px;
    color: white;
}

.stage-area {
    text-align: center;
}

.stage {
    background: linear-gradient(45deg, #ff6b6b, #ff8e8e);
    color: white;
    padding: 15px 30px;
    border-radius: 10px;
    font-weight: bold;
    font-size: 1.2rem;
    box-shadow: 0 4px 15px rgba(255, 107, 107, 0.3);
    display: inline-block;
    min-width: 200px;
}

.zone-title {
    color: #fff;
    font-weight: bold;
    margin-bottom: 10px;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.seats-grid {
    display: grid;
    gap: 8px;
    margin-bottom: 15px;
}

.vip-grid {
    grid-template-columns: repeat(auto-fit, minmax(80px, 1fr));
    background: rgba(255, 215, 0, 0.1);
    padding: 15px;
    border-radius: 10px;
    border: 2px solid #ffd700;
}

.dance-grid {
    grid-template-columns: repeat(auto-fit, minmax(60px, 1fr));
    background: rgba(23, 162, 184, 0.1);
    padding: 20px;
    border-radius: 10px;
    border: 2px solid #17a2b8;
}

.second-floor-grid {
    grid-template-columns: repeat(auto-fit, minmax(70px, 1fr));
    background: rgba(111, 66, 193, 0.1);
    padding: 15px;
    border-radius: 10px;
    border: 2px solid #6f42c1;
}

.seat {
    width: 60px;
    height: 60px;
    border: 2px solid #ddd;
    border-radius: 8px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 0.8rem;
    text-align: center;
    position: relative;
}

.seat:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 15px rgba(255, 255, 255, 0.2);
}

.seat.vip-seat {
    background: linear-gradient(135deg, #ffd700, #ffed4e);
    color: #000;
    border-color: #ffd700;
}

.seat.dance-seat {
    background: linear-gradient(135deg, #17a2b8, #20c997);
    color: white;
    border-color: #17a2b8;
}

.seat.second-floor-seat {
    background: linear-gradient(135deg, #6f42c1, #8e44ad);
    color: white;
    border-color: #6f42c1;
}

.seat.seat-available {
    opacity: 1;
}

.seat.seat-booked {
    opacity: 0.6;
    background: #ffc107 !important;
    color: #000 !important;
    cursor: not-allowed;
}

.seat.seat-sold {
    opacity: 0.6;
    background: #dc3545 !important;
    color: white !important;
    cursor: not-allowed;
}

.seat.seat-blocked {
    opacity: 0.4;
    background: #6c757d !important;
    color: white !important;
    cursor: not-allowed;
}

.seat.seat-selected {
    transform: scale(1.1);
    box-shadow: 0 0 20px rgba(0, 123, 255, 0.8);
    border-color: #007bff;
    background: #007bff !important;
    color: white !important;
}

.seat-number {
    font-weight: bold;
    font-size: 0.9rem;
}

.seat-price {
    font-size: 0.7rem;
    opacity: 0.8;
    margin-top: 2px;
}
</style>

<?php
$content = ob_get_clean();
$pageTitle = $event['title'];

include 'header.php';
?>
