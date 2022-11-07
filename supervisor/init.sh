#!/bin/bash
rm -f /etc/supervisord.d/ZaiHuaBot-Queue.ini
rm -f /etc/supervisord.d/ZaiHuaBot-Queue-default.ini
rm -f /etc/supervisord.d/ZaiHuaBot-Queue-TelegramLimitedApiRequest.ini
cp /www/wwwroot/ZaiHuaBot/supervisor/ZaiHuaBot-Queue.ini /etc/supervisord.d/
cp /www/wwwroot/ZaiHuaBot/supervisor/ZaiHuaBot-Queue-default.ini /etc/supervisord.d/
cp /www/wwwroot/ZaiHuaBot/supervisor/ZaiHuaBot-Queue-TelegramLimitedApiRequest.ini /etc/supervisord.d/
