#!/bin/bash
rm -f /etc/supervisord.d/SearchBot-Queue.ini
rm -f /etc/supervisord.d/SearchBot-Queue-default.ini
cp /www/wwwroot/SearchBot/supervisor/SearchBot-Queue.ini /etc/supervisord.d/
cp /www/wwwroot/SearchBot/supervisor/SearchBot-Queue-default.ini /etc/supervisord.d/
