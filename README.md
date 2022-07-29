# Telegram-Bot

A PHP Laravel Telegram Bot

# License

[GPL-3.0-Only](LICENSE) <img src="https://github.com/jyxjjj/jyxjjj/blob/main/resources/images/GPL-3.0-only.svg" alt="GNU GPL VERSION 3(GPL-3.0-only)" width="30%" align="center">

# Versions

[![Fedora](https://img.shields.io/badge/Fedora-36-blue.svg?style=flat-square)](https://download.fedoraproject.org/pub/fedora/linux/releases/36/Server/x86_64/iso/Fedora-Server-dvd-x86_64-36-1.5.iso)

[![Nginx](https://img.shields.io/badge/Nginx-^1.23.0-brightgreen.svg?style=flat-square)](https://nginx.org/en/download.html)
[![GCC](https://img.shields.io/badge/GCC-^12.1-yellow.svg?style=flat-square)](https://gcc.gnu.org/onlinedocs/)
[![OpenSSL](https://img.shields.io/badge/OpenSSL-^3.0.3-red.svg?style=flat-square)](https://www.openssl.org/source/)

[![PHP](https://img.shields.io/badge/PHP-^8.1-blue.svg?style=flat-square)](https://www.php.net/downloads.php)
[![Composer](https://img.shields.io/badge/Composer-^2.3.7-blue.svg?style=flat-square)](https://getcomposer.org/)
[![cURL](https://img.shields.io/badge/cURL-^7.82.0-brightgreen.svg?style=flat-square)](https://curl.se/download.html)

[![Laravel](https://img.shields.io/badge/Laravel-^9.18.0-red.svg?style=flat-square)](https://laravel.com/docs/9.x/installation)

[![Mariadb](https://img.shields.io/badge/MariaDB-^10.8.3-yellow.svg?style=flat-square)](https://mariadb.org/download/)

[![Redis](https://img.shields.io/badge/Redis-^7.0.2-red.svg?style=flat-square)](https://redis.io/download)
[![jemalloc](https://img.shields.io/badge/jemalloc-^5.2.1-blue.svg?style=flat-square)](https://github.com/jemalloc/jemalloc/releases)

# Install

I recommend using systemd to manage laravel queue workers,
and systemd-timer to manage laravel schedules.

This is a doc of supervisor + crontab version that laravel recommended.

You can do anything you want.

## Pre-install

Make a file tree like this:

```
/www/server/mariadb/
├── data
└── mysql
    └── my.cnf
/www/server/redis/
├── conf
│   └── redis.conf
└── data
```

Then run:

```bash
podman-compose -f docker-compose.yml up -d
```

If you are using docker, you need to create a bridge network named podman first.

```bash
docker network create --driver bridge podman
docker-compose -f docker-compose.yml up -d
```

## Run

```bash
dnf update --refresh -y
dnf install supervisor podman* cockpit* --refresh -y
systemctl enable --now supervisord.service
systemctl enable --now nginx.service
systemctl enable --now php-fpm.service
systemctl enable --now container-mariadb.service
systemctl enable --now container-redis.service

composer install
chown -R www:www .
chmod -R 755 .
chmod -R 777 bootstrap/cache/
chmod -R 777 storage/

touch .user.ini
vim .user.ini
chown www:www .user.ini
chmod 644 .user.ini
chattr +i .user.ini

php artisan key:generate
vim .env

vim supervisor/TelegramBot-Queue-default.ini
vim supervisor/TelegramBot-Queue-TelegramLimitedApiRequest.ini
chmod +X supervisor/init.sh
chmod +X supervisor/restart.sh
supervisor/init.sh
supervisor/restart.sh
```

#### Database Migration

```bash
php artisan migrate
```

#### GetWebHookInfo

```bash
php artisan command:GetWebhookInfo
```

#### SetWebhook

```bash
php artisan command:SetWebhook
```

#### DeleteWebhook

```bash
php artisan command:DeleteWebhook
```

#### Restart Queue Workers

If you edited anything, you may need to restart queue workers,
to make sure they are using the new configuration,
or the new codes to be run.

Otherwise, they may update to the latest configurations and codes after 3600 seconds at most.

So you can send the signal to restart queue workers,
via the laravel official command:

```bash
php artisan queue:restart
```

Or force restart with supervisor(not recommended):

```bash
supervisor/restart.sh
```

Or you can let bot call the laravel official command:

> Send a message to the bot with the command ```/restart```

# Authors

[@jyxjjj](https://t.me/jyxjjj)

[@bluebird_tg](https://t.me/bluebird_tg)

# Donate

Alipay <img src="https://github.com/jyxjjj/jyxjjj/blob/main/resources/images/alipay.png" alt="Alipay QRCode" height="128" width="128" align="center">
