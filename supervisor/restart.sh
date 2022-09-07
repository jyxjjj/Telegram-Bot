#!/bin/bash
supervisorctl stop TelegramBot-Queue:*
supervisorctl start TelegramBot-Queue:*
