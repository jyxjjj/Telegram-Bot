#!/bin/bash
supervisorctl reread
supervisorctl update
supervisorctl stop TelegramBot-Queue:*
