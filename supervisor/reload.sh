#!/bin/bash
supervisorctl reread
supervisorctl update
supervisorctl stop TelegramBot-Queue-default:*
supervisorctl stop TelegramBot-Queue-TelegramLimitedApiRequest:*
