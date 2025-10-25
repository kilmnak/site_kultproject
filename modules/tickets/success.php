<?php
// Проверка авторизации
if(!isset($_SESSION['user_id'])) {
    header("Location: index.php?module=auth&action=login");
    exit();
}

$eventId = $_GET['event_id'] ?? 0;
$event = $pdo->prepare("SELECT * FROM events WHERE id = ? AND status = 'active'");
$event->execute([$eventId]);
$event = $event->fetch(PDO::FETCH_ASSOC);

if(!$event) {
    die("Мероприятие не найдено");
}

// Обработка покупки
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ticketCount = intval($_POST['ticket_count']);
    
    if($ticketCount < 1 || $ticketCount > 10) {
        $error = "Выберите от 1 до 10 билетов";
    } elseif($ticketCount > $event['available_seats']) {
        $error = "Недостаточно свободных мест";
    } else {
        // Расчет суммы
        $totalAmount = $event['min_price'] * $ticketCount;
        
        // Создание заказа
        $orderId = $pdo->prepare("INSERT INTO orders (user_id, event_id, ticket_count, total_amount) VALUES (?, ?, ?, ?)");
        $orderId->execute([$_SESSION['user_id'], $eventId, $ticketCount, $totalAmount]);
        $orderId = $pdo->lastInsertId();
        
        // Создание билетов
        for($i = 0; $i < $ticketCount; $i++) {
            $seatNumber = "A" . ($i + 1);
            $qrCode = uniqid('ticket_');
            
            $pdo->prepare("INSERT INTO tickets (order_id, event_id, user_id, seat_number, price, qr_code) 
                          VALUES (?, ?, ?, ?, ?, ?)")
                ->execute([$orderId, $eventId, $_SESSION['user_id'], $seatNumber, $event['min_price'], $qrCode]);
        }
        
        // Обновление доступных мест
        $pdo->prepare("UPDATE events SET available_seats = available_seats - ? WHERE id = ?")
            ->execute([$ticketCount, $eventId]);
        
        header("Location: index.php?module=tickets&action=success&order_id=" . $orderId);
        exit();
    }
}
?>

<div class="container">
    <div class="purchase-container">
        <h2>Покупка билетов</h2>
        
        <?php if(isset($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="event-info">
            <h3><?php echo htmlspecialchars($event['title']); ?></h3>
            <p><strong>Дата:</strong> <?php echo date('d.m.Y, H:i', strtotime($event['event_date'])); ?></p>
            <p><strong>Место:</strong> <?php echo htmlspecialchars($event['location']); ?></p>
            <p><strong>Доступно мест:</strong> <?php echo $event['available_seats']; ?></p>
            <p><strong>Цена за билет:</strong> <?php echo $event['min_price']; ?> руб.</p>
        </div>
        
        <form method="POST" class="purchase-form">
            <div class="form-group">
                <label for="ticket_count">Количество билетов:</label>
                <select id="ticket_count" name="ticket_count" required>
                    <?php for($i = 1; $i <= min(10, $event['available_seats']); $i++): ?>
                        <option value="<?php echo $i; ?>"><?php echo $i; ?> билет(ов)</option>
                    <?php endfor; ?>
                </select>
            </div>
            
            <div class="total-amount">
                <strong>Общая сумма: <span id="total"><?php echo $event['min_price']; ?></span> руб.</strong>
            </div>
            
            <button type="submit" class="auth-btn">Перейти к оплате</button>
        </form>
    </div>
</div>

<script>
document.getElementById('ticket_count').addEventListener('change', function() {
    var price = <?php echo $event['min_price']; ?>;
    var count = this.value;
    document.getElementById('total').textContent = price * count;
});
</script>