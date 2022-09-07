#!/bin/bash
supervisorctl reread
supervisorctl update
supervisorctl stop TelegramBot-Queue:*
supervisorctl start TelegramBot-Queue:*
