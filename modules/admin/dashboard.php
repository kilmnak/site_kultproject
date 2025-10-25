<?php
// Проверка прав администратора
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: index.php");
    exit();
}

// Статистика
$totalEvents = $pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalOrders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalRevenue = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE status = 'paid'")->fetchColumn() ?? 0;

// Последние заказы
$recentOrders = $pdo->query("SELECT o.*, u.name as user_name, e.title as event_title 
                            FROM orders o 
                            JOIN users u ON o.user_id = u.id 
                            JOIN events e ON o.event_id = e.id 
                            ORDER BY o.created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <h2>Админ-панель</h2>
    
    <div class="admin-stats">
        <div class="stat-card">
            <h3>Мероприятия</h3>
            <div class="stat-number"><?php echo $totalEvents; ?></div>
        </div>
        
        <div class="stat-card">
            <h3>Пользователи</h3>
            <div class="stat-number"><?php echo $totalUsers; ?></div>
        </div>
        
        <div class="stat-card">
            <h3>Заказы</h3>
            <div class="stat-number"><?php echo $totalOrders; ?></div>
        </div>
        
        <div class="stat-card">
            <h3>Выручка</h3>
            <div class="stat-number"><?php echo number_format($totalRevenue, 2); ?> руб.</div>
        </div>
    </div>
    
    <div class="admin-content">
        <div class="recent-orders">
            <h3>Последние заказы</h3>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Пользователь</th>
                        <th>Мероприятие</th>
                        <th>Сумма</th>
                        <th>Статус</th>
                        <th>Дата</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($recentOrders as $order): ?>
                    <tr>
                        <td>#<?php echo $order['id']; ?></td>
                        <td><?php echo htmlspecialchars($order['user_name']); ?></td>
                        <td><?php echo htmlspecialchars($order['event_title']); ?></td>
                        <td><?php echo $order['total_amount']; ?> руб.</td>
                        <td><span class="status-<?php echo $order['status']; ?>"><?php echo $order['status']; ?></span></td>
                        <td><?php echo date('d.m.Y H:i', strtotime($order['created_at'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="admin-actions">
            <a href="index.php?module=admin&action=events_management" class="btn-primary">Управление мероприятиями</a>
            <a href="index.php?module=admin&action=users_management" class="btn-secondary">Управление пользователями</a>
        </div>
    </div>
</div>