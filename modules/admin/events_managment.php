<?php
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: index.php");
    exit();
}

// Добавление мероприятия
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_event'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $category_id = $_POST['category_id'];
    $event_date = $_POST['event_date'];
    $location = $_POST['location'];
    $min_price = $_POST['min_price'];
    $max_price = $_POST['max_price'];
    $total_seats = $_POST['total_seats'];
    
    $stmt = $pdo->prepare("INSERT INTO events (title, description, category_id, event_date, location, min_price, max_price, total_seats, available_seats) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$title, $description, $category_id, $event_date, $location, $min_price, $max_price, $total_seats, $total_seats]);
    
    header("Location: index.php?module=admin&action=events_management&success=1");
    exit();
}

// Получение категорий
$categories = $pdo->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);
$events = $pdo->query("SELECT e.*, c.name as category_name FROM events e LEFT JOIN categories c ON e.category_id = c.id ORDER BY e.event_date")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <h2>Управление мероприятиями</h2>
    
    <?php if(isset($_GET['success'])): ?>
        <div class="success-message">Мероприятие успешно добавлено!</div>
    <?php endif; ?>
    
    <div class="admin-panel">
        <div class="add-event-form">
            <h3>Добавить мероприятие</h3>
            <form method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label for="title">Название:</label>
                        <input type="text" id="title" name="title" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="category_id">Категория:</label>
                        <select id="category_id" name="category_id" required>
                            <?php foreach($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>"><?php echo $category['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="description">Описание:</label>
                    <textarea id="description" name="description" rows="3" required></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="event_date">Дата и время:</label>
                        <input type="datetime-local" id="event_date" name="event_date" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="location">Место проведения:</label>
                        <input type="text" id="location" name="location" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="min_price">Минимальная цена:</label>
                        <input type="number" id="min_price" name="min_price" step="0.01" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="max_price">Максимальная цена:</label>
                        <input type="number" id="max_price" name="max_price" step="0.01" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="total_seats">Количество мест:</label>
                        <input type="number" id="total_seats" name="total_seats" required>
                    </div>
                </div>
                
                <button type="submit" name="add_event" class="auth-btn">Добавить мероприятие</button>
            </form>
        </div>
        
        <div class="events-list">
            <h3>Список мероприятий</h3>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Название</th>
                        <th>Категория</th>
                        <th>Дата</th>
                        <th>Места</th>
                        <th>Цена</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($events as $event): ?>
                    <tr>
                        <td><?php echo $event['id']; ?></td>
                        <td><?php echo htmlspecialchars($event['title']); ?></td>
                        <td><?php echo $event['category_name']; ?></td>
                        <td><?php echo date('d.m.Y H:i', strtotime($event['event_date'])); ?></td>
                        <td><?php echo $event['available_seats']; ?>/<?php echo $event['total_seats']; ?></td>
                        <td><?php echo $event['min_price']; ?>-<?php echo $event['max_price']; ?> руб.</td>
                        <td>
                            <a href="index.php?module=admin&action=edit_event&id=<?php echo $event['id']; ?>" class="btn-edit">Редактировать</a>
                            <a href="index.php?module=admin&action=delete_event&id=<?php echo $event['id']; ?>" class="btn-delete" onclick="return confirm('Удалить мероприятие?')">Удалить</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>