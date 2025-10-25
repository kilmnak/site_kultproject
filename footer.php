<?php
// footer.php - Отдельный файл футера
// Этот файл подключается в header.php и обеспечивает sticky footer
?>

<footer class="bg-dark text-light py-4 mt-auto">
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <h5><?php echo SITE_NAME; ?></h5>
                <p>Концертное агентство, специализирующееся на организации культурно-массовых мероприятий.</p>
            </div>
            <div class="col-md-4">
                <h5>Контакты</h5>
                <p><i class="fas fa-phone me-2"></i> +7 (495) 123-45-67</p>
                <p><i class="fas fa-envelope me-2"></i> info@kultproject.ru</p>
                <p><i class="fas fa-map-marker-alt me-2"></i> Москва, ул. Тверская, 15</p>
            </div>
            <div class="col-md-4">
                <h5>Следите за нами</h5>
                <div class="social-links">
                    <a href="#" class="text-light me-3" title="Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="text-light me-3" title="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#" class="text-light me-3" title="VKontakte">
                        <i class="fab fa-vk"></i>
                    </a>
                    <a href="#" class="text-light" title="Telegram">
                        <i class="fab fa-telegram"></i>
                    </a>
                </div>
            </div>
        </div>
        <hr class="my-4">
        <div class="row">
            <div class="col-md-6">
                <div class="text-center text-md-start">
                    <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. Все права защищены.</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="text-center text-md-end">
                    <a href="/terms.php" class="text-light me-3">Условия использования</a>
                    <a href="/privacy.php" class="text-light me-3">Политика конфиденциальности</a>
                    <a href="/contact.php" class="text-light">Контакты</a>
                </div>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
