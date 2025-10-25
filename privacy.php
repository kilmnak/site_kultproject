<?php
require_once 'config.php';
require_once 'includes/functions.php';

session_start();

$pageTitle = 'Политика конфиденциальности';
ob_start();
?>

<div class="hero-section bg-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8 mx-auto text-center">
                <h1 class="display-4 fw-bold mb-4">Политика конфиденциальности</h1>
                <p class="lead mb-4">Как мы защищаем и обрабатываем ваши персональные данные</p>
                <small class="opacity-75">Последнее обновление: <?php echo date('d.m.Y'); ?></small>
            </div>
        </div>
    </div>
</div>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="card">
                <div class="card-body p-5">
                    
                    <!-- Введение -->
                    <section class="mb-5">
                        <h2 class="text-primary mb-4">
                            <i class="fas fa-shield-alt me-2"></i>
                            1. Введение
                        </h2>
                        <p>
                            <?php echo SITE_NAME; ?> (далее — «мы», «наш», «нас») серьезно относится к защите 
                            персональных данных наших пользователей. Настоящая Политика конфиденциальности 
                            описывает, как мы собираем, используем, храним и защищаем вашу персональную информацию.
                        </p>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Важно:</strong> Используя наш сервис, вы соглашаетесь с условиями 
                            настоящей Политики конфиденциальности.
                        </div>
                    </section>

                    <!-- Правовые основы -->
                    <section class="mb-5">
                        <h2 class="text-primary mb-4">
                            <i class="fas fa-gavel me-2"></i>
                            2. Правовые основы обработки данных
                        </h2>
                        <p>
                            Мы обрабатываем ваши персональные данные в соответствии с:
                        </p>
                        <ul>
                            <li><strong>Федеральный закон № 152-ФЗ</strong> «О персональных данных»</li>
                            <li><strong>Гражданский кодекс Российской Федерации</strong></li>
                            <li><strong>Трудовой кодекс Российской Федерации</strong></li>
                            <li><strong>Иные нормативные правовые акты</strong> Российской Федерации</li>
                        </ul>
                        
                        <h5>2.1. Принципы обработки данных</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <ul>
                                    <li>Законность и справедливость</li>
                                    <li>Соответствие целям обработки</li>
                                    <li>Соответствие объему и характеру</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul>
                                    <li>Точность и актуальность</li>
                                    <li>Хранение в течение необходимого срока</li>
                                    <li>Конфиденциальность</li>
                                </ul>
                            </div>
                        </div>
                    </section>

                    <!-- Какие данные мы собираем -->
                    <section class="mb-5">
                        <h2 class="text-primary mb-4">
                            <i class="fas fa-database me-2"></i>
                            3. Какие персональные данные мы собираем
                        </h2>
                        
                        <h5>3.1. Данные, предоставляемые вами</h5>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Категория данных</th>
                                        <th>Примеры</th>
                                        <th>Обязательность</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Идентификационные данные</td>
                                        <td>ФИО, дата рождения</td>
                                        <td><span class="badge bg-danger">Обязательно</span></td>
                                    </tr>
                                    <tr>
                                        <td>Контактные данные</td>
                                        <td>Email, телефон, адрес</td>
                                        <td><span class="badge bg-warning">Частично</span></td>
                                    </tr>
                                    <tr>
                                        <td>Платежные данные</td>
                                        <td>Данные карты, банковские реквизиты</td>
                                        <td><span class="badge bg-info">При оплате</span></td>
                                    </tr>
                                    <tr>
                                        <td>Данные о предпочтениях</td>
                                        <td>Интересы, история покупок</td>
                                        <td><span class="badge bg-secondary">Добровольно</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <h5>3.2. Данные, собираемые автоматически</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <ul>
                                    <li><strong>Технические данные:</strong> IP-адрес, браузер, ОС</li>
                                    <li><strong>Данные использования:</strong> страницы, время посещения</li>
                                    <li><strong>Файлы cookie:</strong> настройки, предпочтения</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul>
                                    <li><strong>Логи сервера:</strong> запросы, ошибки</li>
                                    <li><strong>Аналитические данные:</strong> статистика посещений</li>
                                    <li><strong>Данные устройств:</strong> тип, разрешение экрана</li>
                                </ul>
                            </div>
                        </div>
                    </section>

                    <!-- Цели обработки -->
                    <section class="mb-5">
                        <h2 class="text-primary mb-4">
                            <i class="fas fa-target me-2"></i>
                            4. Цели обработки персональных данных
                        </h2>
                        
                        <div class="row">
                            <div class="col-lg-6 mb-4">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h5 class="card-title text-success">
                                            <i class="fas fa-shopping-cart me-2"></i>
                                            Основные цели
                                        </h5>
                                        <ul class="mb-0">
                                            <li>Обработка заказов и бронирований</li>
                                            <li>Предоставление услуг сервиса</li>
                                            <li>Обработка платежей</li>
                                            <li>Отправка уведомлений</li>
                                            <li>Техническая поддержка</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6 mb-4">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h5 class="card-title text-info">
                                            <i class="fas fa-chart-line me-2"></i>
                                            Дополнительные цели
                                        </h5>
                                        <ul class="mb-0">
                                            <li>Улучшение качества сервиса</li>
                                            <li>Аналитика и статистика</li>
                                            <li>Персонализация контента</li>
                                            <li>Маркетинговые исследования</li>
                                            <li>Соблюдение законодательства</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Важно:</strong> Мы не используем ваши данные для целей, 
                            не указанных в настоящей Политике, без вашего согласия.
                        </div>
                    </section>

                    <!-- Способы обработки -->
                    <section class="mb-5">
                        <h2 class="text-primary mb-4">
                            <i class="fas fa-cogs me-2"></i>
                            5. Способы обработки персональных данных
                        </h2>
                        
                        <h5>5.1. Операции с данными</h5>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="text-center p-3 border rounded">
                                    <i class="fas fa-plus-circle fa-2x text-primary mb-2"></i>
                                    <h6>Сбор</h6>
                                    <small class="text-muted">Получение данных от пользователей</small>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="text-center p-3 border rounded">
                                    <i class="fas fa-edit fa-2x text-success mb-2"></i>
                                    <h6>Обработка</h6>
                                    <small class="text-muted">Анализ и систематизация</small>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="text-center p-3 border rounded">
                                    <i class="fas fa-save fa-2x text-info mb-2"></i>
                                    <h6>Хранение</h6>
                                    <small class="text-muted">Безопасное сохранение данных</small>
                                </div>
                            </div>
                        </div>
                        
                        <h5>5.2. Технические меры защиты</h5>
                        <ul>
                            <li><strong>Шифрование:</strong> SSL/TLS для передачи данных</li>
                            <li><strong>Хеширование:</strong> паролей и чувствительных данных</li>
                            <li><strong>Контроль доступа:</strong> ограничение доступа к данным</li>
                            <li><strong>Мониторинг:</strong> отслеживание несанкционированного доступа</li>
                            <li><strong>Резервное копирование:</strong> регулярное создание копий</li>
                        </ul>
                    </section>

                    <!-- Передача данных третьим лицам -->
                    <section class="mb-5">
                        <h2 class="text-primary mb-4">
                            <i class="fas fa-share-alt me-2"></i>
                            6. Передача персональных данных третьим лицам
                        </h2>
                        
                        <h5>6.1. Когда мы передаем данные</h5>
                        <div class="alert alert-danger">
                            <i class="fas fa-lock me-2"></i>
                            <strong>Важно:</strong> Мы НЕ продаем и НЕ передаем ваши персональные данные 
                            третьим лицам без вашего явного согласия, за исключением случаев, указанных ниже.
                        </div>
                        
                        <h5>6.2. Исключения</h5>
                        <ul>
                            <li><strong>Платежные системы:</strong> для обработки платежей (данные карт)</li>
                            <li><strong>Организаторы мероприятий:</strong> для предоставления услуг</li>
                            <li><strong>Почтовые сервисы:</strong> для отправки уведомлений</li>
                            <li><strong>Аналитические сервисы:</strong> анонимизированная статистика</li>
                            <li><strong>Государственные органы:</strong> по требованию закона</li>
                        </ul>
                        
                        <h5>6.3. Требования к третьим лицам</h5>
                        <p>
                            Все третьи лица, получающие доступ к вашим данным, обязаны:
                        </p>
                        <ul>
                            <li>Соблюдать требования ФЗ «О персональных данных»</li>
                            <li>Использовать данные только для указанных целей</li>
                            <li>Обеспечивать защиту данных</li>
                            <li>Не передавать данные другим лицам</li>
                        </ul>
                    </section>

                    <!-- Хранение данных -->
                    <section class="mb-5">
                        <h2 class="text-primary mb-4">
                            <i class="fas fa-archive me-2"></i>
                            7. Хранение персональных данных
                        </h2>
                        
                        <h5>7.1. Сроки хранения</h5>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Тип данных</th>
                                        <th>Срок хранения</th>
                                        <th>Основание</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Данные аккаунта</td>
                                        <td>До удаления аккаунта</td>
                                        <td>Договор с пользователем</td>
                                    </tr>
                                    <tr>
                                        <td>История покупок</td>
                                        <td>5 лет</td>
                                        <td>Налоговое законодательство</td>
                                    </tr>
                                    <tr>
                                        <td>Логи сервера</td>
                                        <td>1 год</td>
                                        <td>Техническая необходимость</td>
                                    </tr>
                                    <tr>
                                        <td>Данные платежей</td>
                                        <td>3 года</td>
                                        <td>Финансовое законодательство</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <h5>7.2. Места хранения</h5>
                        <ul>
                            <li><strong>Основные серверы:</strong> Россия, Москва</li>
                            <li><strong>Резервные копии:</strong> Россия, Санкт-Петербург</li>
                            <li><strong>CDN:</strong> Россия, различные регионы</li>
                        </ul>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Важно:</strong> Все данные хранятся на территории Российской Федерации 
                            в соответствии с требованиями законодательства.
                        </div>
                    </section>

                    <!-- Права субъектов данных -->
                    <section class="mb-5">
                        <h2 class="text-primary mb-4">
                            <i class="fas fa-user-shield me-2"></i>
                            8. Ваши права как субъекта персональных данных
                        </h2>
                        
                        <div class="row">
                            <div class="col-lg-6 mb-4">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h5 class="card-title text-primary">
                                            <i class="fas fa-eye me-2"></i>
                                            Право на информацию
                                        </h5>
                                        <ul class="mb-0">
                                            <li>Получать информацию о обработке данных</li>
                                            <li>Знать цели и способы обработки</li>
                                            <li>Получать список третьих лиц</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6 mb-4">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h5 class="card-title text-success">
                                            <i class="fas fa-edit me-2"></i>
                                            Право на изменение
                                        </h5>
                                        <ul class="mb-0">
                                            <li>Требовать уточнения данных</li>
                                            <li>Обновлять информацию в аккаунте</li>
                                            <li>Исправлять неточные данные</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6 mb-4">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h5 class="card-title text-warning">
                                            <i class="fas fa-ban me-2"></i>
                                            Право на ограничение
                                        </h5>
                                        <ul class="mb-0">
                                            <li>Ограничить обработку данных</li>
                                            <li>Отозвать согласие</li>
                                            <li>Заблокировать аккаунт</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6 mb-4">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h5 class="card-title text-danger">
                                            <i class="fas fa-trash me-2"></i>
                                            Право на удаление
                                        </h5>
                                        <ul class="mb-0">
                                            <li>Требовать удаления данных</li>
                                            <li>Удалить аккаунт</li>
                                            <li>Очистить историю</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <h5>8.1. Как реализовать права</h5>
                        <ol>
                            <li>Обратитесь в службу поддержки</li>
                            <li>Укажите конкретное требование</li>
                            <li>Предоставьте документы для идентификации</li>
                            <li>Ожидайте ответа в течение 30 дней</li>
                        </ol>
                    </section>

                    <!-- Cookies -->
                    <section class="mb-5">
                        <h2 class="text-primary mb-4">
                            <i class="fas fa-cookie-bite me-2"></i>
                            9. Использование файлов cookie
                        </h2>
                        
                        <h5>9.1. Что такое cookie</h5>
                        <p>
                            Cookie — это небольшие текстовые файлы, которые сохраняются на вашем устройстве 
                            при посещении веб-сайта. Они помогают нам улучшать работу сайта и персонализировать контент.
                        </p>
                        
                        <h5>9.2. Типы cookie, которые мы используем</h5>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Тип cookie</th>
                                        <th>Назначение</th>
                                        <th>Срок действия</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Необходимые</td>
                                        <td>Основная функциональность сайта</td>
                                        <td>Сессия</td>
                                    </tr>
                                    <tr>
                                        <td>Функциональные</td>
                                        <td>Настройки пользователя</td>
                                        <td>1 год</td>
                                    </tr>
                                    <tr>
                                        <td>Аналитические</td>
                                        <td>Статистика посещений</td>
                                        <td>2 года</td>
                                    </tr>
                                    <tr>
                                        <td>Маркетинговые</td>
                                        <td>Персонализированная реклама</td>
                                        <td>1 год</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <h5>9.3. Управление cookie</h5>
                        <p>
                            Вы можете управлять cookie через настройки браузера:
                        </p>
                        <ul>
                            <li><strong>Chrome:</strong> Настройки → Конфиденциальность → Файлы cookie</li>
                            <li><strong>Firefox:</strong> Настройки → Приватность → Файлы cookie</li>
                            <li><strong>Safari:</strong> Настройки → Конфиденциальность → Файлы cookie</li>
                            <li><strong>Edge:</strong> Настройки → Файлы cookie и разрешения сайтов</li>
                        </ul>
                    </section>

                    <!-- Безопасность -->
                    <section class="mb-5">
                        <h2 class="text-primary mb-4">
                            <i class="fas fa-lock me-2"></i>
                            10. Меры защиты персональных данных
                        </h2>
                        
                        <h5>10.1. Технические меры</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <ul>
                                    <li><strong>Шифрование:</strong> SSL/TLS, AES-256</li>
                                    <li><strong>Контроль доступа:</strong> многоуровневая авторизация</li>
                                    <li><strong>Мониторинг:</strong> 24/7 отслеживание безопасности</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul>
                                    <li><strong>Резервное копирование:</strong> ежедневные бэкапы</li>
                                    <li><strong>Обновления:</strong> регулярные патчи безопасности</li>
                                    <li><strong>Аудит:</strong> периодические проверки</li>
                                </ul>
                            </div>
                        </div>
                        
                        <h5>10.2. Организационные меры</h5>
                        <ul>
                            <li>Обучение сотрудников по защите данных</li>
                            <li>Строгие процедуры доступа к данным</li>
                            <li>Регулярные проверки соответствия</li>
                            <li>Планы реагирования на инциденты</li>
                        </ul>
                        
                        <div class="alert alert-success">
                            <i class="fas fa-shield-alt me-2"></i>
                            <strong>Сертификация:</strong> Наша система соответствует требованиям 
                            PCI DSS для обработки платежных данных.
                        </div>
                    </section>

                    <!-- Изменения политики -->
                    <section class="mb-5">
                        <h2 class="text-primary mb-4">
                            <i class="fas fa-edit me-2"></i>
                            11. Изменения в Политике конфиденциальности
                        </h2>
                        <p>
                            Мы можем обновлять настоящую Политику конфиденциальности. 
                            О существенных изменениях мы уведомим вас:
                        </p>
                        <ul>
                            <li>Через email-рассылку</li>
                            <li>Уведомлением на сайте</li>
                            <li>Обновлением даты в настоящем документе</li>
                        </ul>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Продолжение использования сервиса после изменений означает 
                            согласие с обновленной Политикой конфиденциальности.
                        </div>
                    </section>

                    <!-- Контактная информация -->
                    <section class="mb-0">
                        <h2 class="text-primary mb-4">
                            <i class="fas fa-phone me-2"></i>
                            12. Контактная информация
                        </h2>
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Ответственное лицо по защите данных</h5>
                                <p>
                                    <strong>Иванов Иван Иванович</strong><br>
                                    <i class="fas fa-envelope me-2"></i>
                                    <a href="mailto:dpo@kultproject.ru">dpo@kultproject.ru</a><br>
                                    <i class="fas fa-phone me-2"></i>
                                    <a href="tel:+74951234567">+7 (495) 123-45-67</a>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <h5>Служба поддержки</h5>
                                <p>
                                    <i class="fas fa-envelope me-2"></i>
                                    <a href="mailto:support@kultproject.ru">support@kultproject.ru</a><br>
                                    <i class="fas fa-clock me-2"></i>
                                    Пн-Пт: 9:00-18:00 (МСК)
                                </p>
                            </div>
                        </div>
                        
                        <div class="alert alert-warning mt-4">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Жалобы и обращения:</strong> Если вы считаете, что ваши права 
                            на защиту персональных данных нарушены, вы можете обратиться в 
                            Роскомнадзор или суд.
                        </div>
                    </section>

                </div>
            </div>
            
            <!-- Кнопки действий -->
            <div class="text-center mt-4">
                <div class="card bg-light">
                    <div class="card-body">
                        <h5 class="card-title">Управление данными</h5>
                        <p class="card-text">
                            Хотите узнать больше о ваших данных или реализовать свои права?
                        </p>
                        <a href="/contact.php" class="btn btn-primary me-3">
                            <i class="fas fa-envelope me-2"></i>
                            Связаться с нами
                        </a>
                        <a href="/profile.php" class="btn btn-outline-primary me-3">
                            <i class="fas fa-user-cog me-2"></i>
                            Настройки аккаунта
                        </a>
                        <a href="/terms.php" class="btn btn-outline-secondary">
                            <i class="fas fa-file-contract me-2"></i>
                            Условия использования
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include 'header.php';
?>
