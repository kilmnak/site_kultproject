<?php
// fix-admin-login.php - Исправление проблем с входом в админ-панель
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Исправление проблем с входом в админ-панель</h1>";

// Проверяем подключение к базе данных
echo "<h2>1. Проверка подключения к БД:</h2>";
try {
    require_once 'config.php';
    require_once 'includes/database.php';
    
    $db = Database::getInstance();
    echo "<p>✅ Подключение к БД успешно</p>";
    
} catch (Exception $e) {
    echo "<p>❌ Ошибка подключения к БД: " . $e->getMessage() . "</p>";
    echo "<p>Проверьте настройки в config.php</p>";
    exit;
}

// Проверяем, есть ли таблица users
echo "<h2>2. Проверка таблицы users:</h2>";
try {
    $tables = $db->fetchAll("SHOW TABLES LIKE 'users'");
    if (empty($tables)) {
        echo "<p>❌ Таблица 'users' не найдена</p>";
        echo "<p>Выполняем создание таблицы...</p>";
        
        // Создаем таблицу users
        $sql = "CREATE TABLE users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            first_name VARCHAR(100) NOT NULL,
            last_name VARCHAR(100) NOT NULL,
            phone VARCHAR(20),
            role ENUM('admin', 'manager', 'client') DEFAULT 'client',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            is_active BOOLEAN DEFAULT TRUE
        )";
        
        $db->query($sql);
        echo "<p>✅ Таблица 'users' создана</p>";
    } else {
        echo "<p>✅ Таблица 'users' существует</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Ошибка при проверке/создании таблицы: " . $e->getMessage() . "</p>";
}

// Проверяем пользователей
echo "<h2>3. Проверка пользователей:</h2>";
try {
    $users = $db->fetchAll("SELECT id, email, role, is_active FROM users");
    if (empty($users)) {
        echo "<p>⚠️ Пользователей в БД нет</p>";
        echo "<p>Создаем первого администратора...</p>";
        
        // Создаем первого администратора
        $hashedPassword = password_hash(FIRST_ADMIN_PASSWORD, PASSWORD_DEFAULT);
        
        $db->query(
            "INSERT INTO users (email, password, first_name, last_name, role, is_active) VALUES (?, ?, ?, ?, 'admin', 1)",
            [FIRST_ADMIN_EMAIL, $hashedPassword, FIRST_ADMIN_FIRST_NAME, FIRST_ADMIN_LAST_NAME]
        );
        
        echo "<p>✅ Первый администратор создан</p>";
        echo "<p><strong>Email:</strong> " . FIRST_ADMIN_EMAIL . "</p>";
        echo "<p><strong>Пароль:</strong> " . FIRST_ADMIN_PASSWORD . "</p>";
    } else {
        echo "<p>✅ Найдено пользователей: " . count($users) . "</p>";
        
        // Проверяем, есть ли администраторы
        $admins = $db->fetchAll("SELECT * FROM users WHERE role IN ('admin', 'manager') AND is_active = 1");
        if (empty($admins)) {
            echo "<p>⚠️ Активных администраторов нет</p>";
            echo "<p>Создаем администратора...</p>";
            
            $hashedPassword = password_hash(FIRST_ADMIN_PASSWORD, PASSWORD_DEFAULT);
            
            $db->query(
                "INSERT INTO users (email, password, first_name, last_name, role, is_active) VALUES (?, ?, ?, ?, 'admin', 1)",
                [FIRST_ADMIN_EMAIL, $hashedPassword, FIRST_ADMIN_FIRST_NAME, FIRST_ADMIN_LAST_NAME]
            );
            
            echo "<p>✅ Администратор создан</p>";
        } else {
            echo "<p>✅ Активные администраторы найдены</p>";
        }
    }
} catch (Exception $e) {
    echo "<p>❌ Ошибка при работе с пользователями: " . $e->getMessage() . "</p>";
}

// Тестируем логин
echo "<h2>4. Тест логина:</h2>";
try {
    $testEmail = FIRST_ADMIN_EMAIL;
    $testPassword = FIRST_ADMIN_PASSWORD;
    
    // Проверяем пользователя
    $user = $db->fetch(
        "SELECT * FROM users WHERE email = ? AND role IN ('admin', 'manager') AND is_active = 1",
        [$testEmail]
    );
    
    if (!$user) {
        echo "<p>❌ Пользователь с email " . $testEmail . " не найден или неактивен</p>";
    } else {
        echo "<p>✅ Пользователь найден</p>";
        echo "<p>Роль: " . $user['role'] . "</p>";
        echo "<p>Активен: " . ($user['is_active'] ? 'Да' : 'Нет') . "</p>";
        
        // Проверяем пароль
        if (password_verify($testPassword, $user['password'])) {
            echo "<p>✅ Пароль верный</p>";
            echo "<p><strong>Логин должен работать!</strong></p>";
        } else {
            echo "<p>❌ Пароль неверный</p>";
            echo "<p>Обновляем пароль...</p>";
            
            $hashedPassword = password_hash($testPassword, PASSWORD_DEFAULT);
            $db->query(
                "UPDATE users SET password = ? WHERE email = ?",
                [$hashedPassword, $testEmail]
            );
            
            echo "<p>✅ Пароль обновлен</p>";
        }
    }
} catch (Exception $e) {
    echo "<p>❌ Ошибка при тестировании логина: " . $e->getMessage() . "</p>";
}

echo "<h2>5. Результат:</h2>";
echo "<p>Если все проверки прошли успешно, попробуйте войти в админ-панель:</p>";
echo "<p><strong>Email:</strong> " . FIRST_ADMIN_EMAIL . "</p>";
echo "<p><strong>Пароль:</strong> " . FIRST_ADMIN_PASSWORD . "</p>";

echo "<p><a href='admin-login.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Войти в админ-панель</a></p>";

echo "<h2>6. Если проблема остается:</h2>";
echo "<ul>";
echo "<li>Очистите кэш браузера (Ctrl+F5)</li>";
echo "<li>Проверьте, что файл config.php содержит правильные настройки БД</li>";
echo "<li>Убедитесь, что MySQL сервер запущен</li>";
echo "<li>Проверьте права доступа к файлам</li>";
echo "</ul>";
?>
