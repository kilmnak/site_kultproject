<?php
// admin-footer.php - Отдельный файл футера для админ-панели
// Этот файл подключается в админских страницах
?>

<footer class="bg-dark text-light py-3 mt-auto">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-md-6">
                <div class="text-center text-md-start">
                    <small>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?> Админ. Все права защищены.</small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="text-center text-md-end">
                    <small>
                        <a href="../" class="text-light me-3">На сайт</a>
                        <a href="../contact.php" class="text-light me-3">Поддержка</a>
                        <span class="text-muted">Версия 1.0.0</span>
                    </small>
                </div>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
