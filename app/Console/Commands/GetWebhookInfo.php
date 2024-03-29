<?php
/**
 * DESMG Telegram Bot
 * This file is a part of our Open Source Project (https://github.com/jyxjjj/Telegram-Bot)
 *
 * @copyright 2015-2024 DESMG Co., Ltd.
 * @license GNU General Public License v3.0 (https://www.gnu.org/licenses/gpl-3.0.html)
 * @author DESMG Co., Ltd. (www.desmg.com) < opensource@desmg.org >
 *
 * @QQ 773933146
 * @Telegram jyxjjj (https://t.me/jyxjjj)
 * @Producer DESMG
 *
 * Copyright (C) 2015-2024 DESMG
 * All Rights Reserved.
 *
 * 🇨🇳 🇬🇧 🇳🇱
 * Terms of Service: https://www.desmg.com/policies/terms
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
use Illuminate\Console\Command;
use Longman\TelegramBot\Entities\WebhookInfo;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;

class GetWebhookInfo extends Command
{
    protected $signature = 'command:GetWebhookInfo';
    protected $description = 'Get Webhook Info https://core.telegram.org/bots/api#getwebhookinfo';

    /**
     * @return int
     */
    public function handle(): int
    {
        try {
            BotCommon::getTelegram();
            $request = Request::getWebhookInfo();
            if (!$request->isOk()) {
                throw new TelegramException($request->getDescription());
            }
            /** @var $result WebhookInfo */
            $result = $request->getResult();
            $url = $result->getUrl() == '' ? 'Not set' : $result->getUrl();
            $ip = $result->getIpAddress() == '' ? '<empty>' : $result->getIpAddress();
            $has_custom_certificate = $result->getHasCustomCertificate() ? 'true' : 'false';
            $pending_update_count = $result->getPendingUpdateCount();
            $last_error_date = $result->getLastErrorDate() == '' ? '<empty>' : $result->getLastErrorDate();
            $last_error_message = $result->getLastErrorMessage() == '' ? '<empty>' : $result->getLastErrorMessage();
            $max_connections = $result->getMaxConnections() == '' ? '40' : $result->getMaxConnections();
            $allowed_updates = count($result->getAllowedUpdates() ?? []) == 0 ? '<empty>' : ' - ' . implode("\n - ", $result->getAllowedUpdates());
            self::info("URL: $url");
            self::info("IP: $ip");
            self::info("Has custom certificate: $has_custom_certificate");
            self::info("Pending update count: $pending_update_count");
            self::info("Last error date: $last_error_date");
            self::info("Last error message: $last_error_message");
            self::info("Max connections: $max_connections");
            self::info('Allowed updates: ');
            self::info($allowed_updates);
        } catch (TelegramException $e) {
            self::error($e->getMessage());
        }
        return self::SUCCESS;
    }
}
