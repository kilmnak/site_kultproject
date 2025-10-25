<?php
// Обработка формы входа
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    // Поиск пользователя в БД
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];
        
        header("Location: index.php");
        exit();
    } else {
        $error = "Неверный email или пароль";
    }
}
?>

<div class="auth-container">
    <div class="auth-form">
        <h2>Вход в личный кабинет</h2>
        
        <?php if(isset($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Пароль:</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="auth-btn">Войти</button>
        </form>
        
        <p>Нет аккаунта? <a href="index.php?module=auth&action=register">Зарегистрируйтесь</a></p>
    </div>
</div>