<?php
/**
 * DESMG Telegram Bot
 * This file is a part of our Open Source Project (https://github.com/jyxjjj/Telegram-Bot)
 *
 * @copyright 2015-2024 DESMG
 * @license GNU General Public License v3.0 (https://www.gnu.org/licenses/gpl-3.0.html)
 * @author DESMG (www.desmg.com) < opensource@desmg.org >
 *
 * @QQ 773933146
 * @Telegram jyxjjj (https://t.me/jyxjjj)
 * @Producer DESMG
 *
 * Copyright (C) 2015-2024 DESMG
 * All Rights Reserved.
 *
 * 🇨🇳 🇬🇧 🇳🇱
 * Addon License: https://www.desmg.com/policies/license
 *
 * Released under GNU General Public License Version 3.0
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Console\Commands;

use App\Common\BotCommon;
use DESMG\RFC4122\UUID;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;

class SetWebhook extends Command
{
    protected $signature = 'command:SetWebhook {--u|update-token}';
    protected $description = 'Set Webhook https://core.telegram.org/bots/api#setwebhook';

    /**
     * @return int
     */
    public function handle(): int
    {
        $url = env('TELEGRAM_API_URI') . '/api/webhook';
        $max_connections = 25;
        $allowed_updates = [
            'message',
            'edited_message',
            'channel_post',
            'edited_channel_post',
            'callback_query',
            'my_chat_member',
            'chat_member',
            'chat_join_request',
        ];
        $origin_token = env('HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN');
        if ($this->option('update-token')) {
            $secret_token = UUID::DEID64();
            $this->setSecret($secret_token);
        } else {
            if (strlen($origin_token) < 64) {
                $secret_token = UUID::DEID64();
                $this->setSecret($secret_token);
            } else {
                $secret_token = $origin_token;
            }
        }
        self::info("Secret token: $secret_token");
        try {
            BotCommon::getTelegram();
            $webHookInfo = [
                'url' => $url,
                'max_connections' => $max_connections,
                'allowed_updates' => $allowed_updates,
                'drop_pending_updates' => true,
                'secret_token' => $secret_token,
            ];
            if (env('TELEGRAM_CERTIFICATE') != null) {
                $webHookInfo['certificate'] = base_path(env('TELEGRAM_CERTIFICATE'));
                self::info("Custom Certificate: {$webHookInfo['certificate']}");
            }
            if (env('TELEGRAM_IPADDRESS') != null) {
                $webHookInfo['ip_address'] = env('TELEGRAM_IPADDRESS');
                self::info("Force IP Address: {$webHookInfo['ip_address']}");
            }
            $result = Request::setWebhook($webHookInfo);
            self::info($result->getDescription());
        } catch (TelegramException $e) {
            self::error($e->getMessage());
        }
        return self::SUCCESS;
    }

    /**
     * @param string $data
     * @return void
     */
    protected function setSecret(string $data): void
    {
        $filename = App::environmentFilePath();
        $content = file_get_contents($filename);
        $origin = env('HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN');
        self::info("Origin: $origin");
        self::info("New: $data");
        $content = preg_replace(
            "/^HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN=$origin/m",
            "HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN=$data",
            $content
        );
        file_put_contents($filename, $content);
    }
}
