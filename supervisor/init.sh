#!/bin/bash
rm -f /etc/supervisord.d/TelegramBot-Queue.ini
rm -f /etc/supervisord.d/TelegramBot-Queue-default.ini
cp /www/wwwroot/TelegramBot/supervisor/TelegramBot-Queue.ini /etc/supervisord.d/
cp /www/wwwroot/TelegramBot/supervisor/TelegramBot-Queue-default.ini /etc/supervisord.d/
