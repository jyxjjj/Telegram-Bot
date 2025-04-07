# LICENSE CHANGE

As of 2024-08-01, the license for Telegram-Bot has been updated
from GNU General Public License Version 3 [(GPLv3)](https://www.gnu.org/licenses/gpl-3.0)
to GNU Affero General Public License Version 3 [(AGPLv3)](https://www.gnu.org/licenses/agpl-3.0).

This change ensures that any use of the software over a network must also provide the corresponding source code,
in line with our commitment to software freedom.

# Recently Notice

We have deleted Searchable Bots and Contribute 3.0 Bots, and we will not provide any support for them.

Contribute 2.0 Codes are still available, but we will not provide any support for them.

It will soon be transfer to other open source enthusiasts.

We always make our codes open source, and we do not earn any money from them.

It is hard to maintain the codes, and we have to pay for the server.

Main Bot is still available, and under maintenance.

But it will have more private code and less open source code.

This will not affect any of your usage or development, all migration will happen on new codes.

The main reason to open this project is to make a framework instead of a full bot.

After we give such a lot of functions, we found that we have no time to maintain them.

And more and more APIs are not free to use.

So we have no more time to maintain such a lot of bots.

We are sorry for the inconvenience.

If you like our codes, you can donate to us via the donate button this README file contains or right side of this repository.

Thank you for your support.

# Telegram-Bot

A PHP Laravel Telegram Bot

* [License](#license)
* [Versions](#versions)
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

[AGPL-3.0-Only](LICENSE)

# Versions

[![DigitalOcean](https://web-platforms.sfo2.cdn.digitaloceanspaces.com/WWW/Badge%201.svg)](https://m.do.co/c/23e8653b361a)

[![Fedora](https://img.shields.io/badge/Fedora-41-blue.svg?style=flat-square)](https://getfedora.org)

[![PHP](https://img.shields.io/badge/PHP-^8.4-purple.svg?style=flat-square)](https://www.php.net/downloads.php)

[![Mariadb](https://img.shields.io/badge/MariaDB-^11.4-yellow.svg?style=flat-square)](https://mariadb.org/download/)

[![Redis](https://img.shields.io/badge/Redis-^7.4-red.svg?style=flat-square)](https://redis.io/download)

# Install

I recommend using SystemD to manage laravel queue workers,
and SystemD-Timer to manage laravel schedules.

This is a doc of SystemD + Crontab version.

You can do anything you want.

## Pre-install

* All this repo commands are tested on Fedora 41

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
cp mariadb.container /etc/containers/systemd/mariadb.container
cp redis.container /etc/containers/systemd/redis.container
systemctl daemon-reload
systemctl start mariadb.service
systemctl start redis.service
```

## Run

```bash
dnf update --refresh -y
dnf install podman* cockpit* --refresh -y
systemctl enable --now nginx.service
systemctl enable --now php-fpm.service

composer install
./permission.sh

php artisan key:generate
php artisan queue:install
cp -n .env.example .env
vim .env
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
php artisan horizon:terminate
```

Or you can let bot call the laravel official command:

> Send a message to the bot with the command ```/restart```

# Authors

[@jyxjjj](https://t.me/jyxjjj)

[@bluebird_tg](https://t.me/bluebird_tg)

# Donate

<a href="https://www.desmg.com/donate">Click Here</a>
