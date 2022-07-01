# Telegram-Bot

A PHP Laravel Telegram Bot

[![Fedora](https://img.shields.io/badge/Fedora-36-blue.svg?style=flat-square)](https://download.fedoraproject.org/pub/fedora/linux/releases/36/Server/x86_64/iso/Fedora-Server-dvd-x86_64-36-1.5.iso)

[![GCC](https://img.shields.io/badge/GCC-^12.1-yellow.svg?style=flat-square)](https://gcc.gnu.org/onlinedocs/)

[![OpenSSL](https://img.shields.io/badge/OpenSSL-^3.0.3-red.svg?style=flat-square)](https://www.openssl.org/source/)

[![Nginx](https://img.shields.io/badge/Nginx-^1.23.0-brightgreen.svg?style=flat-square)](https://nginx.org/en/download.html)

[![cURL](https://img.shields.io/badge/cURL-^7.82.0-brightgreen.svg?style=flat-square)](https://curl.se/download.html)

[![PHP](https://img.shields.io/badge/PHP-^8.1-blue.svg?style=flat-square)](https://www.php.net/downloads.php)

[![Composer](https://img.shields.io/badge/Composer-^2.3.7-blue.svg?style=flat-square)](https://getcomposer.org/)

[![Laravel](https://img.shields.io/badge/Laravel-^9.18.0-red.svg?style=flat-square)](https://laravel.com/docs/9.x/installation)

[![Mariadb](https://img.shields.io/badge/MariaDB-^10.8.3-yellow.svg?style=flat-square)](https://mariadb.org/download/)

[![Redis](https://img.shields.io/badge/Redis-^7.0.2-red.svg?style=flat-square)](https://redis.io/download)

[![jemalloc](https://img.shields.io/badge/jemalloc-^5.2.1-blue.svg?style=flat-square)](https://github.com/jemalloc/jemalloc/releases)

# Install

```bash
dnf update --refresh -y
dnf install supervisor podman* cockpit* --refresh -y

composer install
vim .env
chown -R www:www .
chmod -R 755 .
chmod -R 777 bootstrap/cache/
chmod -R 777 storage/

touch .user.ini
vim .user.ini
chown www:www .user.ini
chmod 644 .user.ini
chattr +i .user.ini

vim supervisor/TelegramBot-Queue-default.ini
vim supervisor/TelegramBot-Queue-TelegramLimitedApiRequest.ini
chmod +X supervisor/init.sh
chmod +X supervisor/restart.sh
supervisor/init.sh
supervisor/restart.sh
```

## GetWebHookInfo

```bash
php artisan command:GetWebhookInfo
```

## SetWebhook

```bash
php artisan command:SetWebhook
```

## DeleteWebhook

```bash
php artisan command:DeleteWebhook
```

