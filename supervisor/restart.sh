#!/bin/bash
supervisorctl reread
supervisorctl update
supervisorctl stop TelegramBot-Queue-default:*
supervisorctl stop TelegramBot-Queue-TelegramLimitedApiRequest:*
supervisorctl start TelegramBot-Queue-default:*
supervisorctl start TelegramBot-Queue-TelegramLimitedApiRequest:*
