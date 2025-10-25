<?php
// Определяем правильный путь к config.php
$configPath = __DIR__ . '/../config.php';
if (!file_exists($configPath)) {
    $configPath = 'config.php'; // Fallback для случаев, когда файл вызывается из корня
}
require_once $configPath;
require_once __DIR__ . '/database.php';

class Auth {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function login($email, $password) {
        $user = $this->db->fetch(
            "SELECT * FROM users WHERE email = ? AND is_active = 1",
            [$email]
        );
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            return true;
        }
        
        return false;
    }
    
    public function register($email, $password, $firstName, $lastName, $phone = null) {
        // Проверяем, существует ли пользователь
        $existingUser = $this->db->fetch(
            "SELECT id FROM users WHERE email = ?",
            [$email]
        );
        
        if ($existingUser) {
            return false; // Пользователь уже существует
        }
        
        // Создаем нового пользователя
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $this->db->query(
            "INSERT INTO users (email, password, first_name, last_name, phone) VALUES (?, ?, ?, ?, ?)",
            [$email, $hashedPassword, $firstName, $lastName, $phone]
        );
        
        return $this->db->lastInsertId();
    }
    
    public function logout() {
        session_destroy();
        return true;
    }
    
    // Админские методы
    public function adminLogin($email, $password) {
        // Проверяем, есть ли пользователи в системе
        $userCount = $this->db->fetch("SELECT COUNT(*) as count FROM users")['count'];
        
        // Если пользователей нет, создаем первого администратора
        if ($userCount == 0) {
            $this->createFirstAdmin();
        }
        
        // Проверяем логин администратора
        $user = $this->db->fetch(
            "SELECT * FROM users WHERE email = ? AND role IN ('admin', 'manager') AND is_active = 1",
            [$email]
        );
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['is_admin'] = true;
            return true;
        }
        
        return false;
    }
    
    private function createFirstAdmin() {
        $hashedPassword = password_hash(FIRST_ADMIN_PASSWORD, PASSWORD_DEFAULT);
        
        $this->db->query(
            "INSERT INTO users (email, password, first_name, last_name, role, is_active) VALUES (?, ?, ?, ?, 'admin', 1)",
            [FIRST_ADMIN_EMAIL, $hashedPassword, FIRST_ADMIN_FIRST_NAME, FIRST_ADMIN_LAST_NAME]
        );
    }
    
    public function requireRole($requiredRole) {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
            header('Location: ../admin-login.php');
            exit;
        }
        
        $userRole = $_SESSION['user_role'];
        
        // Проверяем права доступа
        if ($requiredRole === 'admin' && $userRole !== 'admin') {
            header('Location: ../admin-login.php?error=access_denied');
            exit;
        }
        
        if ($requiredRole === 'manager' && !in_array($userRole, ['admin', 'manager'])) {
            header('Location: ../admin-login.php?error=access_denied');
            exit;
        }
    }
    
    public function isAdmin() {
        return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
    }
    
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        return $this->db->fetch(
            "SELECT * FROM users WHERE id = ?",
            [$_SESSION['user_id']]
        );
    }
    
    public function hasRole($role) {
        return $this->isLoggedIn() && $_SESSION['user_role'] === $role;
    }
    
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header('Location: /login.php');
            exit;
        }
    }
    
    public function requireUserRole($role) {
        $this->requireLogin();
        if (!$this->hasRole($role)) {
            header('Location: /403.php');
            exit;
        }
    }
}

class EventManager {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function createEvent($data) {
        $sql = "INSERT INTO events (title, description, event_date, venue, address, max_capacity, base_price, organizer_id, status, image_url, venue_layout) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $this->db->query($sql, [
            $data['title'],
            $data['description'],
            $data['event_date'],
            $data['venue'],
            $data['address'],
            $data['max_capacity'],
            $data['base_price'],
            $data['organizer_id'] ?? null,
            $data['status'] ?? 'draft',
            $data['image_url'] ?? null,
            $data['venue_layout'] ?? 'none'
        ]);
        
        return $this->db->lastInsertId();
    }
    
    public function updateEvent($id, $data) {
        $sql = "UPDATE events SET title = ?, description = ?, event_date = ?, venue = ?, address = ?, 
                max_capacity = ?, base_price = ?, status = ?, image_url = ?, venue_layout = ?, updated_at = CURRENT_TIMESTAMP 
                WHERE id = ?";
        
        $stmt = $this->db->query($sql, [
            $data['title'],
            $data['description'],
            $data['event_date'],
            $data['venue'],
            $data['address'],
            $data['max_capacity'],
            $data['base_price'],
            $data['status'],
            $data['image_url'] ?? null,
            $data['venue_layout'] ?? 'none',
            $id
        ]);
        
        return $stmt->rowCount() > 0;
    }
    
    public function getEvent($id) {
        return $this->db->fetch(
            "SELECT * FROM events WHERE id = ?",
            [$id]
        );
    }
    
    public function getEvents($status = null, $limit = null, $offset = 0) {
        $sql = "SELECT * FROM events";
        $params = [];
        
        if ($status) {
            $sql .= " WHERE status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY event_date ASC";
        
        if ($limit) {
            $sql .= " LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
        }
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function deleteEvent($id) {
        return $this->db->query("DELETE FROM events WHERE id = ?", [$id]);
    }
    
    public function getUpcomingEvents($limit = 10) {
        return $this->db->fetchAll(
            "SELECT * FROM events WHERE event_date > NOW() AND status = 'published' ORDER BY event_date ASC LIMIT ?",
            [$limit]
        );
    }
}

class BookingManager {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function createBooking($userId, $eventId, $seatIds) {
        $this->db->beginTransaction();
        
        try {
            // Генерируем код бронирования
            $bookingCode = $this->generateBookingCode();
            
            // Проверяем доступность мест
            $availableSeats = $this->db->fetchAll(
                "SELECT id, price_category_id FROM seats WHERE id IN (" . implode(',', array_fill(0, count($seatIds), '?')) . ") AND status = 'available'",
                $seatIds
            );
            
            if (count($availableSeats) !== count($seatIds)) {
                throw new Exception("Некоторые места недоступны");
            }
            
            // Рассчитываем общую стоимость
            $totalAmount = 0;
            foreach ($availableSeats as $seat) {
                $price = $this->db->fetch(
                    "SELECT price FROM price_categories WHERE id = ?",
                    [$seat['price_category_id']]
                );
                $totalAmount += $price['price'];
            }
            
            // Создаем бронирование
            $this->db->query(
                "INSERT INTO bookings (user_id, event_id, booking_code, expires_at, total_amount) VALUES (?, ?, ?, ?, ?)",
                [$userId, $eventId, $bookingCode, date('Y-m-d H:i:s', time() + BOOKING_TIMEOUT), $totalAmount]
            );
            
            $bookingId = $this->db->lastInsertId();
            
            // Создаем заказы билетов
            foreach ($availableSeats as $seat) {
                $price = $this->db->fetch(
                    "SELECT price FROM price_categories WHERE id = ?",
                    [$seat['price_category_id']]
                );
                
                $this->db->query(
                    "INSERT INTO ticket_orders (booking_id, seat_id, price) VALUES (?, ?, ?)",
                    [$bookingId, $seat['id'], $price['price']]
                );
                
                // Бронируем место
                $this->db->query(
                    "UPDATE seats SET status = 'booked' WHERE id = ?",
                    [$seat['id']]
                );
            }
            
            $this->db->commit();
            return $bookingId;
            
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    public function confirmBooking($bookingId, $paymentData) {
        $this->db->beginTransaction();
        
        try {
            $booking = $this->db->fetch(
                "SELECT * FROM bookings WHERE id = ? AND status = 'pending'",
                [$bookingId]
            );
            
            if (!$booking) {
                throw new Exception("Бронирование не найдено или уже обработано");
            }
            
            // Обновляем статус бронирования
            $this->db->query(
                "UPDATE bookings SET status = 'confirmed' WHERE id = ?",
                [$bookingId]
            );
            
            // Создаем запись о платеже
            $this->db->query(
                "INSERT INTO payments (booking_id, amount, payment_method, payment_status, transaction_id, payment_data) 
                 VALUES (?, ?, ?, 'completed', ?, ?)",
                [$bookingId, $booking['total_amount'], $paymentData['method'], $paymentData['transaction_id'], json_encode($paymentData)]
            );
            
            // Обновляем статус мест на "продано"
            $this->db->query(
                "UPDATE seats s 
                 JOIN ticket_orders t ON s.id = t.seat_id 
                 SET s.status = 'sold' 
                 WHERE t.booking_id = ?",
                [$bookingId]
            );
            
            // Создаем билеты
            $orders = $this->db->fetchAll(
                "SELECT * FROM ticket_orders WHERE booking_id = ?",
                [$bookingId]
            );
            
            foreach ($orders as $order) {
                $ticketCode = $this->generateTicketCode();
                $qrCode = $this->generateQRCode($ticketCode);
                
                $this->db->query(
                    "INSERT INTO tickets (order_id, ticket_code, qr_code) VALUES (?, ?, ?)",
                    [$order['id'], $ticketCode, $qrCode]
                );
            }
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    public function getBooking($id) {
        return $this->db->fetch(
            "SELECT b.*, e.title as event_title, e.event_date, e.venue 
             FROM bookings b 
             JOIN events e ON b.event_id = e.id 
             WHERE b.id = ?",
            [$id]
        );
    }
    
    public function getUserBookings($userId) {
        return $this->db->fetchAll(
            "SELECT b.*, e.title as event_title, e.event_date, e.venue 
             FROM bookings b 
             JOIN events e ON b.event_id = e.id 
             WHERE b.user_id = ? 
             ORDER BY b.created_at DESC",
            [$userId]
        );
    }
    
    private function generateBookingCode() {
        return 'BK' . strtoupper(substr(md5(uniqid()), 0, 8));
    }
    
    private function generateTicketCode() {
        return 'TK' . strtoupper(substr(md5(uniqid()), 0, 12));
    }
    
    private function generateQRCode($ticketCode) {
        // Здесь должна быть интеграция с библиотекой генерации QR-кодов
        return 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($ticketCode);
    }
}

class NotificationManager {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function sendNotification($userId, $type, $subject, $message) {
        $this->db->query(
            "INSERT INTO notifications (user_id, type, subject, message) VALUES (?, ?, ?, ?)",
            [$userId, $type, $subject, $message]
        );
        
        // Здесь должна быть логика отправки email/SMS
        return $this->db->lastInsertId();
    }
    
    public function getUserNotifications($userId, $limit = 10) {
        return $this->db->fetchAll(
            "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?",
            [$userId, $limit]
        );
    }
}

class AnalyticsManager {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function getEventStatistics($eventId) {
        return $this->db->fetch(
            "SELECT 
                COUNT(t.id) as tickets_sold,
                SUM(p.amount) as total_revenue,
                AVG(r.rating) as average_rating
             FROM events e
             LEFT JOIN bookings b ON e.id = b.event_id AND b.status = 'confirmed'
             LEFT JOIN ticket_orders t ON b.id = t.booking_id
             LEFT JOIN payments p ON b.id = p.booking_id AND p.payment_status = 'completed'
             LEFT JOIN reviews r ON e.id = r.event_id
             WHERE e.id = ?",
            [$eventId]
        );
    }
    
    public function getSalesReport($startDate, $endDate) {
        return $this->db->fetchAll(
            "SELECT 
                DATE(p.created_at) as date,
                COUNT(DISTINCT p.booking_id) as bookings_count,
                SUM(p.amount) as daily_revenue
             FROM payments p
             WHERE p.payment_status = 'completed' 
             AND p.created_at BETWEEN ? AND ?
             GROUP BY DATE(p.created_at)
             ORDER BY date ASC",
            [$startDate, $endDate]
        );
    }
    
    public function getTopEvents($limit = 10) {
        return $this->db->fetchAll(
            "SELECT 
                e.title,
                COUNT(t.id) as tickets_sold,
                SUM(p.amount) as revenue
             FROM events e
             LEFT JOIN bookings b ON e.id = b.event_id AND b.status = 'confirmed'
             LEFT JOIN ticket_orders t ON b.id = t.booking_id
             LEFT JOIN payments p ON b.id = p.booking_id AND p.payment_status = 'completed'
             GROUP BY e.id, e.title
             ORDER BY revenue DESC
             LIMIT ?",
            [$limit]
        );
    }
}

// Утилиты
function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

function formatPrice($price) {
    return number_format($price, 0, ',', ' ') . ' ₽';
}

function formatDate($date, $format = 'd.m.Y H:i') {
    return date($format, strtotime($date));
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function showMessage($message, $type = 'info') {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
}

function getMessage() {
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        $type = $_SESSION['message_type'] ?? 'info';
        unset($_SESSION['message'], $_SESSION['message_type']);
        return ['message' => $message, 'type' => $type];
    }
    return null;
}
?>
