#!/bin/bash

echo "Введите ваш домен:"
read DOMAIN

# Установка необходимых пакетов
sudo apt-get update
sudo apt-get install -y php7.4-cli php-zip apache2 certbot python3-certbot-apache ffmpeg libimage-exiftool-perl git curl

# Установка Composer
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php --install-dir=/usr/local/bin --filename=composer
php -r "unlink('composer-setup.php');"

# Клонирование репозитория
git clone https://github.com/sevakode/video-uniquizer-bot.git
cd video-uniquizer-bot

# Установка зависимостей
composer install

# Создание и настройка файла .env
echo "Введите ваш API-ключ бота:"
read API_KEY
echo "API_KEY=$API_KEY" > .env

# Настройка Apache
sudo bash -c "cat > /etc/apache2/sites-available/video-uniquizer-bot.conf << EOL
<VirtualHost *:80>
    ServerName $DOMAIN
    DocumentRoot $(pwd)
    <Directory $(pwd)>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
EOL"

# Включение нового виртуального хоста и перезапуск Apache
sudo a2ensite video-uniquizer-bot
sudo systemctl reload apache2

# Получение SSL-сертификата
sudo certbot --apache -d $DOMAIN

echo "Установка и настройка завершены. Бот доступен по адресу https://$DOMAIN"
