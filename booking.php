<?php
require_once 'config.php';
require_once 'includes/functions.php';

session_start();

$auth = new Auth();
$auth->requireLogin();

$bookingManager = new BookingManager();

$bookingId = intval($_GET['id'] ?? 0);
$booking = $bookingManager->getBooking($bookingId);

if (!$booking || $booking['user_id'] != $_SESSION['user_id']) {
    showMessage('Бронирование не найдено', 'error');
    redirect('/');
}

// Проверяем, не истекло ли время бронирования
if (strtotime($booking['expires_at']) < time()) {
    showMessage('Время бронирования истекло', 'error');
    redirect('/');
}

// Обработка оплаты
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'process_payment') {
    $paymentMethod = $_POST['payment_method'] ?? 'card';
    
    try {
        // Заглушка для оплаты - всегда успешна
        $paymentData = [
            'method' => $paymentMethod,
            'transaction_id' => 'TXN_' . time() . '_' . rand(1000, 9999),
            'card_number' => $paymentMethod === 'card' ? '****' : null,
            'amount' => $booking['total_amount']
        ];
        
        // Подтверждаем бронирование и создаем билеты
        $bookingManager->confirmBooking($bookingId, $paymentData);
        
        // Показываем сообщение об успехе
        showMessage('Билеты успешно куплены! Вы можете увидеть их в разделе "Мои билеты".', 'success');
        redirect('/my-tickets.php');
    } catch (Exception $e) {
        showMessage('Ошибка при обработке платежа: ' . $e->getMessage(), 'error');
    }
}

// Получаем детали заказа
$db = Database::getInstance();
$orderDetails = $db->fetchAll(
    "SELECT `to`.*, s.seat_number, s.`row_number`, s.section, pc.name as category_name 
     FROM ticket_orders `to` 
     JOIN seats s ON `to`.seat_id = s.id 
     LEFT JOIN price_categories pc ON s.price_category_id = pc.id 
     WHERE `to`.booking_id = ?",
    [$bookingId]
);

ob_start();
?>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Оплата билетов</h4>
                </div>
                <div class="card-body">
                    <!-- Информация о мероприятии -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5><?php echo htmlspecialchars($booking['event_title']); ?></h5>
                            <p class="text-muted">
                                <i class="fas fa-calendar me-2"></i>
                                <?php echo formatDate($booking['event_date'], 'd.m.Y H:i'); ?>
                            </p>
                            <p class="text-muted">
                                <i class="fas fa-map-marker-alt me-2"></i>
                                <?php echo htmlspecialchars($booking['venue']); ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="text-muted">
                                <strong>Код бронирования:</strong> <?php echo htmlspecialchars($booking['booking_code']); ?>
                            </p>
                            <p class="text-muted">
                                <strong>Действительно до:</strong> 
                                <?php echo formatDate($booking['expires_at'], 'd.m.Y H:i'); ?>
                            </p>
                        </div>
                    </div>
                    
                    <!-- Выбранные места -->
                    <div class="mb-4">
                        <h6>Выбранные места:</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Место</th>
                                        <th>Ряд</th>
                                        <th>Сектор</th>
                                        <th>Категория</th>
                                        <th>Цена</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orderDetails as $order): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($order['seat_number']); ?></td>
                                            <td><?php echo htmlspecialchars($order['row_number']); ?></td>
                                            <td><?php echo htmlspecialchars($order['section']); ?></td>
                                            <td><?php echo htmlspecialchars($order['category_name']); ?></td>
                                            <td><?php echo formatPrice($order['price']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr class="table-primary">
                                        <th colspan="4">Итого:</th>
                                        <th><?php echo formatPrice($booking['total_amount']); ?></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Форма оплаты -->
                    <form method="POST" id="paymentForm">
                        <input type="hidden" name="action" value="process_payment">
                        
                        <h6>Способ оплаты:</h6>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" id="card" value="card" checked>
                                <label class="form-check-label" for="card">
                                    <i class="fas fa-credit-card me-2"></i>
                                    Банковская карта
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" id="cash" value="cash">
                                <label class="form-check-label" for="cash">
                                    <i class="fas fa-money-bill me-2"></i>
                                    Наличные в кассе
                                </label>
                            </div>
                        </div>
                        
                        <div class="alert alert-success">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Тестовый режим:</strong> Оплата проходит автоматически. 
                            Билеты будут доступны в разделе "Мои билеты".
                        </div>
                        
                        <button type="submit" class="btn btn-success btn-lg w-100">
                            <i class="fas fa-check-circle me-2"></i>
                            Подтвердить покупку <?php echo formatPrice($booking['total_amount']); ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- Информация о безопасности -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Безопасность платежей</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-shield-alt text-success me-2"></i>
                        <small>SSL-шифрование</small>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-lock text-success me-2"></i>
                        <small>Защита данных</small>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        <small>PCI DSS сертификация</small>
                    </div>
                </div>
            </div>
            
            <!-- Контакты поддержки -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Нужна помощь?</h6>
                </div>
                <div class="card-body">
                    <p class="small mb-2">
                        <i class="fas fa-phone me-2"></i>
                        +7 (495) 123-45-67
                    </p>
                    <p class="small mb-2">
                        <i class="fas fa-envelope me-2"></i>
                        support@kultproject.ru
                    </p>
                    <p class="small mb-0">
                        <i class="fas fa-clock me-2"></i>
                        Круглосуточно
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Простая валидация формы
    const paymentForm = document.getElementById('paymentForm');
    if (paymentForm) {
        paymentForm.addEventListener('submit', function(e) {
            if (!confirm('Подтвердите покупку билетов?')) {
                e.preventDefault();
            }
        });
    }
});
</script>

<?php
$content = ob_get_clean();
$pageTitle = 'Оплата билетов';

include 'header.php';
?>
