#!/bin/bash
supervisorctl start TelegramBot-Queue-default:*
supervisorctl start TelegramBot-Queue-TelegramLimitedApiRequest:*
