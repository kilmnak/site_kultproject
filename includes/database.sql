-- Создание базы данных
CREATE DATABASE IF NOT EXISTS kultprosvet CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE kultprosvet;

-- Таблица пользователей
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Таблица категорий мероприятий
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Таблица мероприятий
CREATE TABLE events (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    category_id INT,
    event_date DATETIME NOT NULL,
    location VARCHAR(255) NOT NULL,
    venue_layout TEXT, -- Схема зала в JSON
    min_price DECIMAL(10,2) NOT NULL,
    max_price DECIMAL(10,2) NOT NULL,
    total_seats INT NOT NULL,
    available_seats INT NOT NULL,
    image VARCHAR(255),
    status ENUM('active', 'cancelled', 'completed') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- Таблица заказов
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    event_id INT,
    total_amount DECIMAL(10,2) NOT NULL,
    ticket_count INT NOT NULL,
    status ENUM('pending', 'paid', 'cancelled', 'refunded') DEFAULT 'pending',
    payment_method VARCHAR(50),
    payment_status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (event_id) REFERENCES events(id)
);

-- Таблица билетов
CREATE TABLE tickets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT,
    event_id INT,
    user_id INT,
    seat_number VARCHAR(10),
    price DECIMAL(10,2) NOT NULL,
    qr_code VARCHAR(255),
    status ENUM('active', 'used', 'cancelled') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (event_id) REFERENCES events(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Таблица платежей
CREATE TABLE payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT,
    amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    transaction_id VARCHAR(100),
    status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id)
);

-- Вставка тестовых данных
INSERT INTO categories (name, description) VALUES
('concerts', 'Музыкальные концерты и выступления'),
('theater', 'Театральные постановки'),
('festivals', 'Фестивали и массовые мероприятия'),
('exhibitions', 'Выставки и экспозиции');

INSERT INTO events (title, description, category_id, event_date, location, min_price, max_price, total_seats, available_seats, image) VALUES
('Концерт симфонического оркестра', 'Великолепное исполнение классических произведений', 1, '2024-02-15 19:00:00', 'Концертный зал им. Чайковского', 1500, 5000, 500, 450, 'concert1.jpg'),
('Театральная премьера "Гамлет"', 'Современная интерпретация классической пьесы', 2, '2024-02-20 18:30:00', 'Московский художественный театр', 2000, 8000, 300, 280, 'theater1.jpg'),
('Фестиваль современного искусства', 'Выставка современных художников и перформансы', 3, '2024-02-25 12:00:00', 'Центр современной культуры', 800, 2000, 1000, 950, 'festival1.jpg'),
('Выставка "Искусство эпохи Возрождения"', 'Шедевры живописи и скульптуры', 4, '2024-03-01 10:00:00', 'Государственный музей изобразительных искусств', 500, 1500, 200, 180, 'exhibition1.jpg');

-- Создание администратора
INSERT INTO users (name, email, password, role) VALUES 
('Администратор', 'admin@kultprosvet.ru', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
-- Пароль: password