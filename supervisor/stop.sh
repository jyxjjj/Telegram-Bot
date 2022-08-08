#!/bin/bash
supervisorctl stop TelegramBot-Queue-default:*
supervisorctl stop TelegramBot-Queue-TelegramLimitedApiRequest:*
