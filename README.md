# video-uniquizer-bot
=======

Этот проект представляет собой телеграм-бота, который преобразует отправленные ему видео, делая их уникальными путем изменения различных параметров, таких как скорость воспроизведения, контраст, насыщенность, яркость и других. Бот также изменяет метаданные файла, чтобы сделать его более трудным для обнаружения дубликатов.

## Требования

- PHP 7.4 или выше
- Composer
- FFmpeg
- ExifTool
- Apache2
- Сertbot

## Установка

### 1. Установка PHP

Убедитесь, что у вас установлен PHP 7.4 или выше. Если нет, установите его с помощью пакетного менеджера вашей системы.

#### Для Ubuntu/Debian:

```bash
sudo apt-get update
sudo apt-get install php7.4 php-zip
```

#### Для CentOS/RHEL:

```bash
sudo yum install epel-release
sudo yum install -y https://rpms.remirepo.net/enterprise/remi-release-7.rpm
sudo yum install -y php74 php-zip
```

### 2. Установка Composer

Установите Composer, следуя инструкциям на [официальном сайте](https://getcomposer.org/download/).

### 3. Установка FFmpeg

Установите FFmpeg, следуя инструкциям для вашей операционной системы:

#### Для Ubuntu/Debian:

```bash
sudo apt-get update
sudo apt-get install ffmpeg
```

#### Для CentOS/RHEL:

```bash
sudo yum install epel-release
sudo rpm -v --import http://li.nux.ro/download/nux/RPM-GPG-KEY-nux.ro
sudo yum install nux-dextop-release
sudo yum install ffmpeg ffmpeg-devel
```

### 4. Установка ExifTool

Установите ExifTool, следуя инструкциям для вашей операционной системы:

#### Для Ubuntu/Debian:

```bash
sudo apt-get update
sudo apt-get install libimage-exiftool-perl
```

#### Для CentOS/RHEL:

```bash
sudo yum install perl-Image-ExifTool
```

### 5. Установка и настройка Apache2

#### Для Ubuntu/Debian:

```bash
sudo apt-get update
sudo apt-get install apache2
```

#### Для CentOS/RHEL:

```bash
sudo yum install httpd
```

Запустите Apache и добавьте его в автозагрузку:

#### Для Ubuntu/Debian:

```bash
sudo systemctl start apache2
sudo systemctl enable apache2
```

#### Для CentOS/RHEL:

```bash
sudo systemctl start httpd
sudo systemctl enable httpd
```

### 6. Настройка HTTPS с использованием Let's Encrypt

#### Установка и настройка Certbot

Certbot — это инструмент для получения и управления SSL-сертификатами от Let's Encrypt. Для установки Certbot выполните следующие шаги:

#### Для Ubuntu/Debian:

```bash
sudo apt-get update
sudo apt-get install certbot python3-certbot-apache
```

#### Для CentOS/RHEL:

```bash
sudo yum install certbot python3-certbot-apache
```

#### Получение SSL-сертификата от Let's Encrypt

После установки Certbot выполните следующую команду для получения и настройки SSL-сертификата:

```bash
sudo certbot --apache
```

Следуйте инструкциям на экране для завершения настройки SSL-сертификата. В процессе вам потребуется ввести адрес электронной почты и доменное имя, для которого вы хотите получить сертификат.

Certbot автоматически настроит Apache для работы с HTTPS.

### 7. Клонирование репозитория и установка зависимостей

Клонируйте репозиторий:

```bash
git clone https://github.com/sevakode/video-uniquizer-bot.git
```

Установите зависимости с помощью Composer:

```bash
cd video-uniquizer-bot
composer install
```

### 8. Настройка бота

Создайте и настройте файл `.env` с вашим API-ключом бота и именем бота. Пример содержимого:

```php
API_KEY=6133333793:AAXXAj-jjXxxmcboLxxxxxxxxuZFgfbaguI
```

### 9. Настройка Apache для работы с ботом

Создайте новый файл конфигурации виртуального хоста для вашего бота:

#### Для Ubuntu/Debian:

```bash
sudo nano /etc/apache2/sites-available/video-uniquizer-bot.conf
```

#### Для CentOS/RHEL:

```bash
sudo nano /etc/httpd/conf.d/video-uniquizer-bot.conf
```

Добавьте следующее содержимое в файл, заменив `your_domain` на ваше доменное имя и `/path/to/video-uniquizer-bot` на путь к папке с вашим проектом:

```
<VirtualHost *:80>
    ServerName your_domain
    DocumentRoot /path/to/video-uniquizer-bot
    <Directory /path/to/video-uniquizer-bot>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Активируйте виртуальный хост:

#### Для Ubuntu/Debian:

```bash
sudo a2ensite video-uniquizer-bot
sudo systemctl reload apache2
```

#### Для CentOS/RHEL:

```bash
sudo systemctl restart httpd
```

### 10. Настройка вебхука для телеграм-бота через URL-адрес браузера

Для настройки вебхука с помощью одного перехода по URL в браузере, вам нужно будет создать URL, содержащий токен бота и URL-адрес, по которому будет отправляться вебхук.

Следуйте этим шагам:

1. Создайте URL в следующем формате:

```
https://api.telegram.org/bot<Your_API_Token>/setWebhook?url=https://<your_domain>/index.php
```

Замените `<Your_API_Token>` на токен вашего телеграм-бота, `<your_domain>` на ваше доменное имя.

Пример:

```
https://api.telegram.org/bot6133333793:AAXXAj-jjXxxmcboLxxxxxxxxuZFgfbaguI/setWebhook?url=https://example.com/index.php
```

2. Вставьте созданный URL в адресную строку вашего браузера и нажмите Enter. Telegram будет уведомлен о новом вебхуке, и вы должны увидеть ответ, подтверждающий успешное добавление вебхука:

```
{"ok":true,"result":true,"description":"Webhook was set"}
```

Теперь ваш телеграм-бот должен быть настроен для работы с вебхуком.

## Использование бота и команды /flip для управления отзеркаливанием

#### Использование бота

Для использования бота следуйте этим шагам:

1. Найдите вашего бота в Telegram, используя его имя (например, @your_bot_name).
2. Откройте чат с вашим ботом и отправьте ему видео, которое вы хотите сделать уникальным.
3. Бот обработает видео, применяя различные фильтры и изменяя метаданные. Если включено отзеркаливание, видео будет отзеркалено по горизонтали.
4. После завершения обработки, бот отправит вам уникализированное видео в виде архива.

#### Использование команды /flip

Команда `/flip` используется для включения или отключения функции отзеркаливания видео. Для управления этой функцией, следуйте этим шагам:

1. Откройте чат с вашим ботом.
2. Отправьте команду `/flip` в чат. Бот прочитает текущий статус отзеркаливания из файла конфигурации или переменной окружения, изменит его и сохранит обратно в файле конфигурации.
3. Бот отправит вам сообщение с обновленным статусом отзеркаливания (включено или выключено).

Теперь, когда вы отправляете видео для уникализации, функция отзеркаливания будет применяться в соответствии с текущим статусом. Если отзеркаливание включено, видео будет отзеркалено по горизонтали. Если отзеркаливание выключено, видео останется без изменений в этом аспекте.
>>>>>>> dc4af9c (init)
