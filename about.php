<?php
require_once 'config.php';
require_once 'includes/functions.php';

session_start();

$pageTitle = 'О нас';
ob_start();
?>

<div class="hero-section bg-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8 mx-auto text-center">
                <h1 class="display-4 fw-bold mb-4">О компании <?php echo SITE_NAME; ?></h1>
                <p class="lead mb-4">Мы создаем незабываемые культурные впечатления уже более 10 лет</p>
            </div>
        </div>
    </div>
</div>

<div class="container my-5">
    <!-- История компании -->
    <div class="row mb-5">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-body p-5">
                    <h2 class="card-title text-center mb-4">
                        <i class="fas fa-history text-primary me-2"></i>
                        Наша история
                    </h2>
                    <p class="lead text-center mb-4">
                        <?php echo SITE_NAME; ?> — это ведущая платформа для организации и продажи билетов на культурные мероприятия в России.
                    </p>
                    <p>
                        Основанная в 2013 году, наша компания начала с организации небольших концертов и театральных постановок. 
                        Сегодня мы являемся надежным партнером для сотен артистов, театров, концертных залов и культурных центров по всей стране.
                    </p>
                    <p>
                        Наша миссия — сделать культуру доступной для каждого, предоставляя удобную платформу для покупки билетов 
                        и создавая незабываемые впечатления от посещения мероприятий.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Наши ценности -->
    <div class="row mb-5">
        <div class="col-12">
            <h2 class="text-center mb-5">
                <i class="fas fa-heart text-primary me-2"></i>
                Наши ценности
            </h2>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card h-100 text-center">
                <div class="card-body">
                    <div class="mb-3">
                        <i class="fas fa-users fa-3x text-primary"></i>
                    </div>
                    <h5 class="card-title">Доступность</h5>
                    <p class="card-text">
                        Мы верим, что культура должна быть доступна каждому, независимо от возраста, социального статуса или места проживания.
                    </p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card h-100 text-center">
                <div class="card-body">
                    <div class="mb-3">
                        <i class="fas fa-star fa-3x text-warning"></i>
                    </div>
                    <h5 class="card-title">Качество</h5>
                    <p class="card-text">
                        Мы работаем только с проверенными партнерами и гарантируем высокое качество всех мероприятий.
                    </p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card h-100 text-center">
                <div class="card-body">
                    <div class="mb-3">
                        <i class="fas fa-handshake fa-3x text-success"></i>
                    </div>
                    <h5 class="card-title">Надежность</h5>
                    <p class="card-text">
                        Мы гарантируем безопасность покупок и честность в отношениях с клиентами и партнерами.
                    </p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card h-100 text-center">
                <div class="card-body">
                    <div class="mb-3">
                        <i class="fas fa-lightbulb fa-3x text-info"></i>
                    </div>
                    <h5 class="card-title">Инновации</h5>
                    <p class="card-text">
                        Мы постоянно развиваемся и внедряем новые технологии для улучшения пользовательского опыта.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Наша команда -->
    <div class="row mb-5">
        <div class="col-12">
            <h2 class="text-center mb-5">
                <i class="fas fa-user-friends text-primary me-2"></i>
                Наша команда
            </h2>
        </div>
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card text-center">
                <div class="card-body">
                    <div class="mb-3">
                        <i class="fas fa-user-tie fa-4x text-primary"></i>
                    </div>
                    <h5 class="card-title">Анна Петрова</h5>
                    <p class="text-muted">Генеральный директор</p>
                    <p class="card-text">
                        Опыт работы в сфере культуры более 15 лет. Выпускница ГИТИСа, 
                        бывший директор крупного театрального центра.
                    </p>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card text-center">
                <div class="card-body">
                    <div class="mb-3">
                        <i class="fas fa-user-cog fa-4x text-success"></i>
                    </div>
                    <h5 class="card-title">Михаил Сидоров</h5>
                    <p class="text-muted">Технический директор</p>
                    <p class="card-text">
                        IT-специалист с 10-летним опытом. Отвечает за развитие технологической 
                        платформы и внедрение инноваций.
                    </p>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card text-center">
                <div class="card-body">
                    <div class="mb-3">
                        <i class="fas fa-user-graduate fa-4x text-info"></i>
                    </div>
                    <h5 class="card-title">Елена Козлова</h5>
                    <p class="text-muted">Директор по маркетингу</p>
                    <p class="card-text">
                        Специалист по продвижению культурных мероприятий. 
                        Работала с ведущими театрами и концертными залами Москвы.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Статистика -->
    <div class="row mb-5">
        <div class="col-12">
            <h2 class="text-center mb-5">
                <i class="fas fa-chart-bar text-primary me-2"></i>
                Наши достижения
            </h2>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card text-center bg-primary text-white">
                <div class="card-body">
                    <h3 class="display-4 fw-bold">500+</h3>
                    <p class="mb-0">Мероприятий в год</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card text-center bg-success text-white">
                <div class="card-body">
                    <h3 class="display-4 fw-bold">50K+</h3>
                    <p class="mb-0">Довольных клиентов</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card text-center bg-info text-white">
                <div class="card-body">
                    <h3 class="display-4 fw-bold">200+</h3>
                    <p class="mb-0">Партнеров</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card text-center bg-warning text-white">
                <div class="card-body">
                    <h3 class="display-4 fw-bold">10</h3>
                    <p class="mb-0">Лет опыта</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Сертификаты и награды -->
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-body p-5 text-center">
                    <h2 class="card-title mb-4">
                        <i class="fas fa-award text-primary me-2"></i>
                        Награды и сертификаты
                    </h2>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="border rounded p-3">
                                <i class="fas fa-medal fa-2x text-warning mb-2"></i>
                                <h6>Лучший билетный оператор 2023</h6>
                                <small class="text-muted">Премия "Культурная столица"</small>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="border rounded p-3">
                                <i class="fas fa-certificate fa-2x text-success mb-2"></i>
                                <h6>ISO 9001:2015</h6>
                                <small class="text-muted">Сертификат качества</small>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="border rounded p-3">
                                <i class="fas fa-shield-alt fa-2x text-info mb-2"></i>
                                <h6>PCI DSS</h6>
                                <small class="text-muted">Безопасность платежей</small>
                            </div>
                        </div>
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
