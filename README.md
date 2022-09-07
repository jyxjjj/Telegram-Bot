# Telegram-Bot

A PHP Laravel Telegram Bot

* [License](#license)
* [Versions](#versions)
* [TODO](#todo)
* [Install](#install)
    * [Pre-install](#pre-install)
    * [Run](#run)
        * [Database Migration](#database-migration)
        * [GetWebHookInfo](#getwebhookinfo)
        * [SetWebhook](#setwebhook)
        * [DeleteWebhook](#deletewebhook)
        * [Restart Queue Workers](#restart-queue-workers)
* [Authors](#authors)
* [Donate](#donate)

# License

[GPL-3.0-Only](LICENSE) <img src="https://github.com/jyxjjj/jyxjjj/blob/main/resources/images/GPL-3.0-only.svg" alt="GNU GPL VERSION 3(GPL-3.0-only)" width="30%" align="center">

# Versions

[![DigitalOcean Referral Badge](https://web-platforms.sfo2.cdn.digitaloceanspaces.com/WWW/Badge%201.svg)](https://www.digitalocean.com/?refcode=23e8653b361a&utm_campaign=Referral_Invite&utm_medium=Referral_Program&utm_source=badge)

[![Fedora](https://img.shields.io/badge/Fedora-36-blue.svg?style=flat-square)](https://download.fedoraproject.org/pub/fedora/linux/releases/36/Server/x86_64/iso/Fedora-Server-dvd-x86_64-36-1.5.iso)

[![PHP](https://img.shields.io/badge/PHP-^8.1-blue.svg?style=flat-square)](https://www.php.net/downloads.php)

[![Mariadb](https://img.shields.io/badge/MariaDB-^10.8-yellow.svg?style=flat-square)](https://mariadb.org/download/)

[![Redis](https://img.shields.io/badge/Redis-^7.0-red.svg?style=flat-square)](https://redis.io/download)

# TODO

- [ ] Support both channel and group
- [ ] Migrate to MarkdownV2

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
chmod +X supervisor/reload.sh
chmod +X supervisor/start.sh
chmod +X supervisor/stop.sh
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
