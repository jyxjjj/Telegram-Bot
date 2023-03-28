#!/bin/bash
rm -f /etc/supervisord.d/YPBot-Queue.ini
rm -f /etc/supervisord.d/YPBot-Queue-default.ini
cp /www/wwwroot/YPBot/supervisor/YPBot-Queue.ini /etc/supervisord.d/
cp /www/wwwroot/YPBot/supervisor/YPBot-Queue-default.ini /etc/supervisord.d/
