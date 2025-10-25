<?php
require_once 'config.php';
require_once 'includes/functions.php';

session_start();

$auth = new Auth();

// Если пользователь уже авторизован, перенаправляем на главную
if ($auth->isLoggedIn()) {
    redirect('/');
}

$error = '';
$success = '';

if ($_POST) {
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $firstName = sanitizeInput($_POST['first_name'] ?? '');
    $lastName = sanitizeInput($_POST['last_name'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    
    // Валидация
    if (empty($email) || empty($password) || empty($firstName) || empty($lastName)) {
        $error = 'Заполните все обязательные поля';
    } elseif ($password !== $confirmPassword) {
        $error = 'Пароли не совпадают';
    } elseif (strlen($password) < 6) {
        $error = 'Пароль должен содержать минимум 6 символов';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Введите корректный email';
    } else {
        $userId = $auth->register($email, $password, $firstName, $lastName, $phone);
        if ($userId) {
            $success = 'Регистрация успешна! Теперь вы можете войти в систему.';
        } else {
            $error = 'Пользователь с таким email уже существует';
        }
    }
}

ob_start();
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow">
                <div class="card-body">
                    <h2 class="card-title text-center mb-4">Регистрация</h2>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            <?php echo $success; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">Имя *</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" required 
                                       value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">Фамилия *</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" required 
                                       value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="email" name="email" required 
                                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="phone" class="form-label">Телефон</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Пароль *</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <div class="form-text">Минимум 6 символов</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Подтверждение пароля *</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="terms" required>
                            <label class="form-check-label" for="terms">
                                Я согласен с <a href="/terms.php" target="_blank">условиями использования</a>
                            </label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 mb-3">
                            <i class="fas fa-user-plus me-2"></i>
                            Зарегистрироваться
                        </button>
                    </form>
                    
                    <div class="text-center">
                        <p class="mb-0">Уже есть аккаунт? <a href="/login.php">Войти</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = 'Регистрация';

include 'header.php';
?>
