<?php
require_once 'config.php';
require_once 'includes/functions.php';

session_start();

$pageTitle = 'Контакты';
$successMessage = '';
$errorMessage = '';

// Обработка формы обратной связи
if ($_POST && isset($_POST['contact_form'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    // Валидация
    $errors = [];
    
    if (empty($name)) {
        $errors[] = 'Имя обязательно для заполнения';
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Введите корректный email адрес';
    }
    
    if (empty($message)) {
        $errors[] = 'Сообщение обязательно для заполнения';
    }
    
    if (empty($errors)) {
        // Здесь можно добавить отправку email или сохранение в БД
        $successMessage = 'Спасибо за ваше сообщение! Мы свяжемся с вами в ближайшее время.';
        
        // Очистка формы
        $_POST = [];
    } else {
        $errorMessage = implode('<br>', $errors);
    }
}

ob_start();
?>

<div class="hero-section bg-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8 mx-auto text-center">
                <h1 class="display-4 fw-bold mb-4">Свяжитесь с нами</h1>
                <p class="lead mb-4">Мы всегда рады помочь и ответить на ваши вопросы</p>
            </div>
        </div>
    </div>
</div>

<div class="container my-5">
    <!-- Сообщения об успехе/ошибке -->
    <?php if ($successMessage): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?php echo $successMessage; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($errorMessage): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?php echo $errorMessage; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Форма обратной связи -->
        <div class="col-lg-8 mb-5">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-envelope me-2"></i>
                        Написать нам
                    </h4>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="">
                        <input type="hidden" name="contact_form" value="1">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Имя *</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Телефон</label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="subject" class="form-label">Тема</label>
                                <select class="form-select" id="subject" name="subject">
                                    <option value="">Выберите тему</option>
                                    <option value="general" <?php echo ($_POST['subject'] ?? '') === 'general' ? 'selected' : ''; ?>>Общий вопрос</option>
                                    <option value="booking" <?php echo ($_POST['subject'] ?? '') === 'booking' ? 'selected' : ''; ?>>Проблема с бронированием</option>
                                    <option value="payment" <?php echo ($_POST['subject'] ?? '') === 'payment' ? 'selected' : ''; ?>>Проблема с оплатой</option>
                                    <option value="refund" <?php echo ($_POST['subject'] ?? '') === 'refund' ? 'selected' : ''; ?>>Возврат билетов</option>
                                    <option value="partnership" <?php echo ($_POST['subject'] ?? '') === 'partnership' ? 'selected' : ''; ?>>Сотрудничество</option>
                                    <option value="other" <?php echo ($_POST['subject'] ?? '') === 'other' ? 'selected' : ''; ?>>Другое</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="message" class="form-label">Сообщение *</label>
                            <textarea class="form-control" id="message" name="message" rows="5" required><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="privacy" required>
                            <label class="form-check-label" for="privacy">
                                Я согласен с <a href="#" data-bs-toggle="modal" data-bs-target="#privacyModal">политикой конфиденциальности</a> *
                            </label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-paper-plane me-2"></i>
                            Отправить сообщение
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Контактная информация -->
        <div class="col-lg-4">
            <!-- Основные контакты -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-phone me-2"></i>
                        Контактная информация
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6><i class="fas fa-map-marker-alt text-primary me-2"></i>Адрес</h6>
                        <p class="mb-0">
                            Москва, ул. Тверская, 15<br>
                            БЦ "Культурный центр", офис 301
                        </p>
                    </div>
                    
                    <div class="mb-3">
                        <h6><i class="fas fa-phone text-success me-2"></i>Телефон</h6>
                        <p class="mb-0">
                            <a href="tel:+74951234567" class="text-decoration-none">+7 (495) 123-45-67</a><br>
                            <small class="text-muted">Пн-Пт: 9:00-18:00</small>
                        </p>
                    </div>
                    
                    <div class="mb-3">
                        <h6><i class="fas fa-envelope text-info me-2"></i>Email</h6>
                        <p class="mb-0">
                            <a href="mailto:info@kultproject.ru" class="text-decoration-none">info@kultproject.ru</a><br>
                            <a href="mailto:support@kultproject.ru" class="text-decoration-none">support@kultproject.ru</a>
                        </p>
                    </div>
                    
                    <div class="mb-0">
                        <h6><i class="fas fa-clock text-warning me-2"></i>Время работы</h6>
                        <p class="mb-0">
                            Понедельник - Пятница: 9:00 - 18:00<br>
                            Суббота: 10:00 - 16:00<br>
                            Воскресенье: выходной
                        </p>
                    </div>
                </div>
            </div>

            <!-- Социальные сети -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-share-alt me-2"></i>
                        Мы в соцсетях
                    </h5>
                </div>
                <div class="card-body text-center">
                    <div class="row">
                        <div class="col-4 mb-2">
                            <a href="#" class="btn btn-outline-primary btn-sm w-100">
                                <i class="fab fa-vk"></i><br>
                                <small>VK</small>
                            </a>
                        </div>
                        <div class="col-4 mb-2">
                            <a href="#" class="btn btn-outline-info btn-sm w-100">
                                <i class="fab fa-telegram"></i><br>
                                <small>Telegram</small>
                            </a>
                        </div>
                        <div class="col-4 mb-2">
                            <a href="#" class="btn btn-outline-success btn-sm w-100">
                                <i class="fab fa-whatsapp"></i><br>
                                <small>WhatsApp</small>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FAQ -->
            <div class="card">
                <div class="card-header bg-warning text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-question-circle me-2"></i>
                        Частые вопросы
                    </h5>
                </div>
                <div class="card-body">
                    <div class="accordion" id="faqAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faq1">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse1">
                                    Как вернуть билет?
                                </button>
                            </h2>
                            <div id="collapse1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Возврат билетов возможен не позднее чем за 24 часа до мероприятия. 
                                    Обратитесь в службу поддержки.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faq2">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse2">
                                    Как получить электронный билет?
                                </button>
                            </h2>
                            <div id="collapse2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    После оплаты билет придет на указанный email. 
                                    Также его можно скачать в личном кабинете.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faq3">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse3">
                                    Можно ли изменить место?
                                </button>
                            </h2>
                            <div id="collapse3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Изменение места возможно при наличии свободных мест 
                                    и не позднее чем за 2 часа до начала мероприятия.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Карта -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-map me-2"></i>
                        Как нас найти
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="bg-light p-5 text-center">
                        <i class="fas fa-map-marked-alt fa-4x text-muted mb-3"></i>
                        <h5>Интерактивная карта</h5>
                        <p class="text-muted">Здесь будет размещена карта с нашим местоположением</p>
                        <p class="mb-0">
                            <strong>Адрес:</strong> Москва, ул. Тверская, 15<br>
                            <strong>Ближайшее метро:</strong> Тверская, Пушкинская (5 минут пешком)
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно политики конфиденциальности -->
<div class="modal fade" id="privacyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Политика конфиденциальности</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Мы собираем и обрабатываем ваши персональные данные в соответствии с Федеральным законом "О персональных данных".</p>
                <p>Ваши данные используются исключительно для:</p>
                <ul>
                    <li>Обработки заказов и бронирований</li>
                    <li>Отправки уведомлений о мероприятиях</li>
                    <li>Обработки обращений в службу поддержки</li>
                </ul>
                <p>Мы не передаем ваши данные третьим лицам без вашего согласия.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include 'header.php';
?>
