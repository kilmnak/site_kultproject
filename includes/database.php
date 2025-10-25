<?php
require_once 'config.php';

class Database {
    private $pdo;
    
    public function __construct() {
        try {
            $this->pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            die("Ошибка подключения к базе данных: " . $e->getMessage());
        }
    }
    
    // Получить все мероприятия
    public function getEvents($category = null, $search = null, $limit = null) {
        $sql = "SELECT e.*, c.name as category_name 
                FROM events e 
                LEFT JOIN categories c ON e.category_id = c.id 
                WHERE e.event_date >= CURDATE() AND e.status = 'active'";
        $params = [];
        
        if($category) {
            $sql .= " AND c.name = ?";
            $params[] = $category;
        }
        
        if($search) {
            $sql .= " AND (e.title LIKE ? OR e.description LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        $sql .= " ORDER BY e.event_date";
        
        if($limit) {
            $sql .= " LIMIT ?";
            $params[] = $limit;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Получить мероприятие по ID
    public function getEventById($id) {
        $stmt = $this->pdo->prepare("SELECT e.*, c.name as category_name 
                                   FROM events e 
                                   LEFT JOIN categories c ON e.category_id = c.id 
                                   WHERE e.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Создать заказ
    public function createOrder($userId, $eventId, $ticketCount, $totalAmount) {
        $stmt = $this->pdo->prepare("INSERT INTO orders (user_id, event_id, ticket_count, total_amount) 
                                   VALUES (?, ?, ?, ?)");
        $stmt->execute([$userId, $eventId, $ticketCount, $totalAmount]);
        return $this->pdo->lastInsertId();
    }
    
    // Создать билеты
    public function createTickets($orderId, $eventId, $userId, $ticketCount, $price) {
        $tickets = [];
        for($i = 0; $i < $ticketCount; $i++) {
            $seatNumber = "A" . ($i + 1);
            $qrCode = uniqid('ticket_');
            
            $stmt = $this->pdo->prepare("INSERT INTO tickets (order_id, event_id, user_id, seat_number, price, qr_code) 
                                       VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$orderId, $eventId, $userId, $seatNumber, $price, $qrCode]);
            $tickets[] = $this->pdo->lastInsertId();
        }
        return $tickets;
    }
    
    // Обновить доступные места
    public function updateAvailableSeats($eventId, $seatsChange) {
        $stmt = $this->pdo->prepare("UPDATE events SET available_seats = available_seats - ? WHERE id = ?");
        $stmt->execute([$seatsChange, $eventId]);
    }
}
?>