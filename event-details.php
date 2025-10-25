<?php
require_once 'config.php';
require_once 'includes/functions.php';

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
    if (empty($seatIds)) {
        showMessage('Выберите места для покупки', 'error');
    } else {
        try {
            $bookingId = $bookingManager->createBooking($_SESSION['user_id'], $eventId, $seatIds);
            showMessage('Места успешно забронированы! Перейдите к оплате.', 'success');
            redirect('/booking.php?id=' . $bookingId);
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
     ORDER BY s.row_number, s.seat_number",
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
                            Схема зала пока не загружена.
                        </div>
                    <?php else: ?>
                        <form id="bookingForm" method="POST">
                            <input type="hidden" name="action" value="book_tickets">
                            
                            <!-- Легенда -->
                            <div class="row mb-3">
                                <div class="col-12">
                                    <h6>Легенда:</h6>
                                    <div class="d-flex flex-wrap gap-2">
                                        <span class="badge bg-success">Доступно</span>
                                        <span class="badge bg-warning">Забронировано</span>
                                        <span class="badge bg-danger">Продано</span>
                                        <span class="badge bg-secondary">Заблокировано</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Схема мест -->
                            <div class="venue-layout mb-4">
                                <?php
                                $currentRow = null;
                                foreach ($seats as $seat):
                                    if ($currentRow !== $seat['row_number']):
                                        if ($currentRow !== null):
                                            echo '</div>';
                                        endif;
                                        echo '<div class="row mb-2">';
                                        echo '<div class="col-2"><strong>Ряд ' . htmlspecialchars($seat['row_number']) . '</strong></div>';
                                        echo '<div class="col-10">';
                                        $currentRow = $seat['row_number'];
                                    endif;
                                    
                                    $statusClass = [
                                        'available' => 'btn-outline-success',
                                        'booked' => 'btn-outline-warning',
                                        'sold' => 'btn-outline-danger',
                                        'blocked' => 'btn-outline-secondary'
                                    ][$seat['status']] ?? 'btn-outline-secondary';
                                    
                                    $disabled = $seat['status'] !== 'available' ? 'disabled' : '';
                                    $checked = $seat['status'] === 'available' ? '' : 'checked';
                                    
                                    echo '<label class="btn ' . $statusClass . ' me-1 mb-1 seat-btn" style="width: 40px;">';
                                    echo '<input type="checkbox" name="seat_ids[]" value="' . $seat['id'] . '" ' . $disabled . ' ' . $checked . ' class="d-none">';
                                    echo htmlspecialchars($seat['seat_number']);
                                    echo '</label>';
                                endforeach;
                                if ($currentRow !== null):
                                    echo '</div></div>';
                                endif;
                                ?>
                            </div>
                            
                            <!-- Выбранные места -->
                            <div id="selectedSeats" class="mb-3" style="display: none;">
                                <h6>Выбранные места:</h6>
                                <div id="selectedSeatsList"></div>
                                <div id="totalPrice" class="h5 text-primary mt-2"></div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-lg" id="bookButton" disabled>
                                <i class="fas fa-ticket-alt me-2"></i>
                                Забронировать места
                            </button>
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
    
    // Цены мест (должны быть переданы из PHP)
    const seatPrices = <?php echo json_encode(array_column($seats, 'price', 'id')); ?>;
    
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectedSeats);
    });
    
    function updateSelectedSeats() {
        const selectedSeats = Array.from(checkboxes).filter(cb => cb.checked);
        const totalPrice = selectedSeats.reduce((sum, cb) => sum + (seatPrices[cb.value] || 0), 0);
        
        if (selectedSeats.length > 0) {
            selectedSeatsDiv.style.display = 'block';
            selectedSeatsList.innerHTML = selectedSeats.map(cb => {
                const seatElement = cb.closest('.seat-btn');
                return seatElement.textContent.trim();
            }).join(', ');
            totalPriceDiv.textContent = 'Общая стоимость: ' + totalPrice.toLocaleString() + ' ₽';
            bookButton.disabled = false;
        } else {
            selectedSeatsDiv.style.display = 'none';
            bookButton.disabled = true;
        }
    }
});
</script>

<?php
$content = ob_get_clean();
$pageTitle = $event['title'];

include 'header.php';
?>
