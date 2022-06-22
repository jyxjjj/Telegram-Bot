#!/bin/bash
rm -f /etc/supervisord.d/TelegramBot-Queue-default.ini
rm -f /etc/supervisord.d/TelegramBot-Queue-TelegramLimitedApiRequest.ini
cp /www/wwwroot/TelegramBot/supervisor/TelegramBot-Queue-default.ini /etc/supervisord.d/
cp /www/wwwroot/TelegramBot/supervisor/TelegramBot-Queue-TelegramLimitedApiRequest.ini /etc/supervisord.d/
