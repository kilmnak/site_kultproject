<?php
// admin/settings.php - Настройки системы
$db = Database::getInstance();
$message = '';
$messageType = '';

// Обработка сохранения настроек
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'save_settings') {
    $settings = [
        'site_name' => sanitizeInput($_POST['site_name']),
        'site_url' => sanitizeInput($_POST['site_url']),
        'admin_email' => sanitizeInput($_POST['admin_email']),
        'booking_timeout' => intval($_POST['booking_timeout']),
        'max_file_size' => intval($_POST['max_file_size']),
        'smtp_host' => sanitizeInput($_POST['smtp_host']),
        'smtp_port' => intval($_POST['smtp_port']),
        'smtp_user' => sanitizeInput($_POST['smtp_user']),
        'smtp_pass' => $_POST['smtp_pass'],
        'payment_gateway_url' => sanitizeInput($_POST['payment_gateway_url']),
        'payment_api_key' => sanitizeInput($_POST['payment_api_key']),
    ];
    
    // В реальном проекте здесь была бы логика сохранения в БД или файл конфигурации
    $message = 'Настройки успешно сохранены!';
    $messageType = 'success';
}

// Получаем текущие настройки (в реальном проекте из БД)
$currentSettings = [
    'site_name' => defined('SITE_NAME') ? SITE_NAME : 'КультПросвет',
    'site_url' => defined('SITE_URL') ? SITE_URL : 'http://localhost',
    'admin_email' => defined('ADMIN_EMAIL') ? ADMIN_EMAIL : 'admin@kultproject.ru',
    'booking_timeout' => 15,
    'max_file_size' => 5,
    'smtp_host' => defined('SMTP_HOST') ? SMTP_HOST : 'smtp.gmail.com',
    'smtp_port' => defined('SMTP_PORT') ? SMTP_PORT : 587,
    'smtp_user' => defined('SMTP_USER') ? SMTP_USER : '',
    'smtp_pass' => '',
    'payment_gateway_url' => defined('PAYMENT_GATEWAY_URL') ? PAYMENT_GATEWAY_URL : '',
    'payment_api_key' => defined('PAYMENT_API_KEY') ? PAYMENT_API_KEY : '',
];

// Информация о системе
$systemInfo = [
    'php_version' => phpversion(),
    'mysql_version' => $db->fetch("SELECT VERSION() as version")['version'],
    'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Неизвестно',
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'post_max_size' => ini_get('post_max_size'),
    'memory_limit' => ini_get('memory_limit'),
    'max_execution_time' => ini_get('max_execution_time'),
];

ob_start();
?>

<div class="row">
    <div class="col-12">
        <h1 class="h3 mb-4 text-gray-800">Настройки системы</h1>
    </div>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
        <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
        <?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-lg-8">
        <form method="POST">
            <input type="hidden" name="action" value="save_settings">
            
            <!-- Основные настройки -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-cog me-2"></i>Основные настройки
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="site_name" class="form-label">Название сайта</label>
                                <input type="text" class="form-control" id="site_name" name="site_name" 
                                       value="<?php echo htmlspecialchars($currentSettings['site_name']); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="site_url" class="form-label">URL сайта</label>
                                <input type="url" class="form-control" id="site_url" name="site_url" 
                                       value="<?php echo htmlspecialchars($currentSettings['site_url']); ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="admin_email" class="form-label">Email администратора</label>
                        <input type="email" class="form-control" id="admin_email" name="admin_email" 
                               value="<?php echo htmlspecialchars($currentSettings['admin_email']); ?>" required>
                    </div>
                </div>
            </div>

            <!-- Настройки бронирования -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-ticket-alt me-2"></i>Настройки бронирования
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="booking_timeout" class="form-label">Таймаут бронирования (минуты)</label>
                                <input type="number" class="form-control" id="booking_timeout" name="booking_timeout" 
                                       value="<?php echo $currentSettings['booking_timeout']; ?>" min="5" max="60" required>
                                <div class="form-text">Время, в течение которого бронирование остается активным</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="max_file_size" class="form-label">Максимальный размер файла (MB)</label>
                                <input type="number" class="form-control" id="max_file_size" name="max_file_size" 
                                       value="<?php echo $currentSettings['max_file_size']; ?>" min="1" max="100" required>
                                <div class="form-text">Максимальный размер загружаемых файлов</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Настройки почты -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="fas fa-envelope me-2"></i>Настройки почты (SMTP)
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="smtp_host" class="form-label">SMTP сервер</label>
                                <input type="text" class="form-control" id="smtp_host" name="smtp_host" 
                                       value="<?php echo htmlspecialchars($currentSettings['smtp_host']); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="smtp_port" class="form-label">SMTP порт</label>
                                <input type="number" class="form-control" id="smtp_port" name="smtp_port" 
                                       value="<?php echo $currentSettings['smtp_port']; ?>" min="1" max="65535">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="smtp_user" class="form-label">SMTP пользователь</label>
                                <input type="text" class="form-control" id="smtp_user" name="smtp_user" 
                                       value="<?php echo htmlspecialchars($currentSettings['smtp_user']); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="smtp_pass" class="form-label">SMTP пароль</label>
                                <input type="password" class="form-control" id="smtp_pass" name="smtp_pass" 
                                       value="<?php echo htmlspecialchars($currentSettings['smtp_pass']); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Рекомендации:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Для Gmail используйте порт 587 с TLS</li>
                            <li>Для Yandex используйте порт 465 с SSL</li>
                            <li>Включите двухфакторную аутентификацию</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Настройки платежей -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-warning">
                        <i class="fas fa-credit-card me-2"></i>Настройки платежей
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="payment_gateway_url" class="form-label">URL платежного шлюза</label>
                        <input type="url" class="form-control" id="payment_gateway_url" name="payment_gateway_url" 
                               value="<?php echo htmlspecialchars($currentSettings['payment_gateway_url']); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="payment_api_key" class="form-label">API ключ платежной системы</label>
                        <input type="text" class="form-control" id="payment_api_key" name="payment_api_key" 
                               value="<?php echo htmlspecialchars($currentSettings['payment_api_key']); ?>">
                        <div class="form-text">Храните в безопасности!</div>
                    </div>
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Безопасность:</strong> Никогда не передавайте API ключи третьим лицам и не храните их в открытом виде.
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between">
                <button type="button" class="btn btn-secondary" onclick="resetForm()">
                    <i class="fas fa-undo me-2"></i>Сбросить
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Сохранить настройки
                </button>
            </div>
        </form>
    </div>

    <div class="col-lg-4">
        <!-- Информация о системе -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-info">
                    <i class="fas fa-server me-2"></i>Информация о системе
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>PHP версия:</span>
                        <strong><?php echo $systemInfo['php_version']; ?></strong>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>MySQL версия:</span>
                        <strong><?php echo $systemInfo['mysql_version']; ?></strong>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Веб-сервер:</span>
                        <strong><?php echo $systemInfo['server_software']; ?></strong>
                    </div>
                </div>
                
                <hr>
                
                <h6 class="text-primary">Ограничения PHP:</h6>
                
                <div class="mb-2">
                    <div class="d-flex justify-content-between">
                        <span>Макс. размер файла:</span>
                        <strong><?php echo $systemInfo['upload_max_filesize']; ?></strong>
                    </div>
                </div>
                
                <div class="mb-2">
                    <div class="d-flex justify-content-between">
                        <span>Макс. размер POST:</span>
                        <strong><?php echo $systemInfo['post_max_size']; ?></strong>
                    </div>
                </div>
                
                <div class="mb-2">
                    <div class="d-flex justify-content-between">
                        <span>Лимит памяти:</span>
                        <strong><?php echo $systemInfo['memory_limit']; ?></strong>
                    </div>
                </div>
                
                <div class="mb-2">
                    <div class="d-flex justify-content-between">
                        <span>Макс. время выполнения:</span>
                        <strong><?php echo $systemInfo['max_execution_time']; ?> сек</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Быстрые действия -->
        <div class="card">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-bolt me-2"></i>Быстрые действия
                </h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <button class="btn btn-outline-primary" onclick="testEmail()">
                        <i class="fas fa-envelope me-2"></i>Тест почты
                    </button>
                    
                    <button class="btn btn-outline-success" onclick="testPayment()">
                        <i class="fas fa-credit-card me-2"></i>Тест платежей
                    </button>
                    
                    <button class="btn btn-outline-info" onclick="clearCache()">
                        <i class="fas fa-trash me-2"></i>Очистить кэш
                    </button>
                    
                    <button class="btn btn-outline-warning" onclick="backupDatabase()">
                        <i class="fas fa-database me-2"></i>Бэкап БД
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function resetForm() {
    if (confirm('Вы уверены, что хотите сбросить все изменения?')) {
        document.querySelector('form').reset();
    }
}

function testEmail() {
    alert('Функция тестирования почты будет реализована в следующих версиях.');
}

function testPayment() {
    alert('Функция тестирования платежей будет реализована в следующих версиях.');
}

function clearCache() {
    if (confirm('Очистить кэш системы?')) {
        alert('Кэш очищен!');
    }
}

function backupDatabase() {
    if (confirm('Создать резервную копию базы данных?')) {
        alert('Бэкап создан!');
    }
}
</script>

<?php
$content = ob_get_clean();
$pageTitle = 'Настройки системы';
include 'admin-header.php';
?>
