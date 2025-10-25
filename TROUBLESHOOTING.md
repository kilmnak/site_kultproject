# Устранение проблем при установке проекта "КультПросвет" на Ubuntu 24.04

## Часто встречающиеся проблемы и их решения

### 1. Ошибки SQL синтаксиса (ERROR 1064)

**Проблема:** `ERROR 1064 (42000) at line 58: You have an error in your SQL syntax`

**Причина:** Использование зарезервированных слов MySQL в качестве имен полей

**Решение:** Исправлен файл `includes/database.sql` - все зарезервированные слова заключены в обратные кавычки:
- `row_number` → `` `row_number` ``
- `status` → `` `status` ``
- `type` → `` `type` ``
- `name` → `` `name` ``

**Альтернативное решение:** Если проблема все еще возникает, переименуйте поля:
```sql
-- Вместо row_number используйте seat_row
-- Вместо status используйте seat_status, booking_status и т.д.
-- Вместо type используйте notification_type
-- Вместо name используйте category_name, partner_name
```

### 2. Ошибки подключения к базе данных

**Проблема:** "Ошибка подключения к базе данных"

**Решения:**
```bash
# Проверьте статус MySQL
sudo systemctl status mysql

# Перезапустите MySQL
sudo systemctl restart mysql

# Проверьте подключение
mysql -u kultproject -p

# Проверьте права пользователя
sudo mysql -e "SHOW GRANTS FOR 'kultproject'@'localhost';"
```

### 2. Ошибки прав доступа к файлам

**Проблема:** "Permission denied" или ошибки загрузки файлов

**Решения:**
```bash
# Установите правильные права
sudo chown -R www-data:www-data /var/www/html/kultproject
sudo chmod -R 755 /var/www/html/kultproject
sudo chmod 777 /var/www/html/kultproject/uploads

# Проверьте права
ls -la /var/www/html/kultproject/
```

### 3. Ошибки Apache

**Проблема:** Сайт не открывается или ошибки 500

**Решения:**
```bash
# Проверьте логи Apache
sudo tail -f /var/log/apache2/error.log

# Проверьте конфигурацию
sudo apache2ctl configtest

# Перезапустите Apache
sudo systemctl restart apache2

# Проверьте статус
sudo systemctl status apache2
```

### 4. Ошибки PHP

**Проблема:** Ошибки PHP или отсутствие модулей

**Решения:**
```bash
# Проверьте версию PHP
php -v

# Проверьте установленные модули
php -m | grep -E "(mysql|pdo|json|gd)"

# Установите недостающие модули
sudo apt install php8.3-mysql php8.3-gd php8.3-json -y

# Перезапустите Apache
sudo systemctl restart apache2
```

### 5. Проблемы с mod_rewrite

**Проблема:** URL не работают корректно

**Решения:**
```bash
# Включите mod_rewrite
sudo a2enmod rewrite

# Проверьте .htaccess файл
cat /var/www/html/kultproject/.htaccess

# Перезапустите Apache
sudo systemctl restart apache2
```

### 6. Ошибки импорта базы данных

**Проблема:** Ошибки при импорте SQL файла

**Решения:**
```bash
# Проверьте синтаксис SQL файла
mysql -u kultproject -p kultproject < /var/www/html/kultproject/includes/database.sql

# Альтернативный способ импорта
mysql -u kultproject -p
USE kultproject;
SOURCE /var/www/html/kultproject/includes/database.sql;
```

### 7. Проблемы с файрволом

**Проблема:** Не удается подключиться к сайту

**Решения:**
```bash
# Проверьте статус файрвола
sudo ufw status

# Откройте необходимые порты
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# Перезапустите файрвол
sudo ufw reload
```

### 8. Проблемы с SSL (если используется)

**Проблема:** Ошибки SSL сертификата

**Решения:**
```bash
# Установите Let's Encrypt
sudo apt install certbot python3-certbot-apache -y

# Получите сертификат
sudo certbot --apache -d your-domain.com
```

### 9. Проблемы с производительностью

**Решения:**
```bash
# Включите кэширование в Apache
sudo a2enmod cache
sudo a2enmod expires

# Настройте PHP OPcache
sudo nano /etc/php/8.3/apache2/php.ini
# Найдите и раскомментируйте:
# opcache.enable=1
# opcache.memory_consumption=128
# opcache.max_accelerated_files=4000
```

### 10. Проблемы с загрузкой файлов

**Решения:**
```bash
# Увеличьте лимиты в PHP
sudo nano /etc/php/8.3/apache2/php.ini

# Установите:
upload_max_filesize = 50M
post_max_size = 50M
max_execution_time = 300
memory_limit = 512M

# Перезапустите Apache
sudo systemctl restart apache2
```

## Полезные команды для диагностики

```bash
# Проверка статуса всех сервисов
sudo systemctl status apache2 mysql

# Просмотр логов в реальном времени
sudo tail -f /var/log/apache2/error.log
sudo tail -f /var/log/mysql/error.log

# Проверка портов
sudo netstat -tlnp | grep :80
sudo netstat -tlnp | grep :3306

# Проверка конфигурации Apache
sudo apache2ctl -S

# Тест PHP
echo "<?php phpinfo(); ?>" | sudo tee /var/www/html/kultproject/info.php
# Откройте http://your-server/info.php
# Удалите файл после проверки: sudo rm /var/www/html/kultproject/info.php
```

## Контакты для поддержки

Если проблемы не решаются:
- Проверьте логи: `/var/log/apache2/` и `/var/log/mysql/`
- Убедитесь, что все зависимости установлены
- Проверьте права доступа к файлам
- Убедитесь, что база данных создана и настроена правильно
