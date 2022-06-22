#!/bin/bash
supervisorctl reread
supervisorctl update
supervisorctl start TelegramBot-Queue-default:*
supervisorctl start TelegramBot-Queue-TelegramLimitedApiRequest:*
