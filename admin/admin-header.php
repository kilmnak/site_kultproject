<?php
// admin/admin-header.php - Общий header для админ-панели
$pageTitle = $pageTitle ?? 'Админ-панель';
$page = $page ?? $_GET['page'] ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle . ' - ' . (defined('SITE_NAME') ? SITE_NAME : 'КультПросвет') . ' Админ'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .admin-sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        .admin-sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            margin: 0.25rem 0.5rem;
            transition: all 0.3s ease;
        }
        .admin-sidebar .nav-link:hover {
            color: white;
            background: rgba(255,255,255,0.1);
            transform: translateX(5px);
        }
        .admin-sidebar .nav-link.active {
            color: white;
            background: rgba(78, 115, 223, 0.8);
        }
        .admin-content {
            background: #f8f9fc;
            min-height: 100vh;
        }
        .admin-navbar {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-bottom: 1px solid #e3e6f0;
        }
        .stats-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            border: none;
            transition: transform 0.3s ease;
        }
        .stats-card:hover {
            transform: translateY(-5px);
        }
        .stats-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0">
                <div class="admin-sidebar">
                    <div class="p-4 text-center">
                        <h4 class="text-white mb-0">
                            <i class="fas fa-cog me-2"></i>
                            <?php echo defined('SITE_NAME') ? SITE_NAME : 'КультПросвет'; ?>
                        </h4>
                        <small class="text-white-50">Админ-панель</small>
                    </div>
                    
                    <nav class="nav flex-column">
                        <a href="?page=dashboard" class="nav-link <?php echo $page === 'dashboard' ? 'active' : ''; ?>">
                            <i class="fas fa-tachometer-alt me-2"></i>Панель
                        </a>
                        <a href="?page=events" class="nav-link <?php echo $page === 'events' ? 'active' : ''; ?>">
                            <i class="fas fa-calendar-alt me-2"></i>Мероприятия
                        </a>
                        <a href="?page=users" class="nav-link <?php echo $page === 'users' ? 'active' : ''; ?>">
                            <i class="fas fa-users me-2"></i>Пользователи
                        </a>
                        <a href="?page=analytics" class="nav-link <?php echo $page === 'analytics' ? 'active' : ''; ?>">
                            <i class="fas fa-chart-bar me-2"></i>Аналитика
                        </a>
                        <a href="?page=settings" class="nav-link <?php echo $page === 'settings' ? 'active' : ''; ?>">
                            <i class="fas fa-cog me-2"></i>Настройки
                        </a>
                        
                        <hr class="my-3 text-white-50">
                        
                        <a href="../" class="nav-link">
                            <i class="fas fa-home me-2"></i>На сайт
                        </a>
                        <a href="../logout.php" class="nav-link text-danger">
                            <i class="fas fa-sign-out-alt me-2"></i>Выйти
                        </a>
                    </nav>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 px-0">
                <div class="admin-content">
                    <!-- Top Navbar -->
                    <nav class="navbar admin-navbar">
                        <div class="container-fluid">
                            <div class="d-flex align-items-center">
                                <h5 class="mb-0 text-gray-800">
                                    <?php
                                    switch ($page) {
                                        case 'events':
                                            echo '<i class="fas fa-calendar-alt me-2"></i>Мероприятия';
                                            break;
                                        case 'users':
                                            echo '<i class="fas fa-users me-2"></i>Пользователи';
                                            break;
                                        case 'analytics':
                                            echo '<i class="fas fa-chart-bar me-2"></i>Аналитика';
                                            break;
                                        case 'settings':
                                            echo '<i class="fas fa-cog me-2"></i>Настройки';
                                            break;
                                        default:
                                            echo '<i class="fas fa-tachometer-alt me-2"></i>Панель управления';
                                            break;
                                    }
                                    ?>
                                </h5>
                            </div>
                            
                            <div class="d-flex align-items-center">
                                <div class="dropdown">
                                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="fas fa-user me-2"></i>
                                        <?php echo $_SESSION['user_name'] ?? 'Администратор'; ?>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="../profile.php">
                                            <i class="fas fa-user me-2"></i>Профиль
                                        </a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="../">
                                            <i class="fas fa-home me-2"></i>На сайт
                                        </a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-danger" href="../logout.php">
                                            <i class="fas fa-sign-out-alt me-2"></i>Выйти
                                        </a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </nav>
                    
                    <!-- Page Content -->
                    <div class="p-4">
                        <?php echo $content; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
