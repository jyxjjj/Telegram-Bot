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

namespace App\Http\Controllers;

use App\Common\BotCommon;
use App\Common\IP;
use App\Jobs\SendMessageJob;
use App\Jobs\WebhookJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Exception\TelegramException;

class IndexController extends BaseController
{
    /**
     * @return Response
     */
    public function index(): Response
    {
        return $this->plain(IP::getClientIpInfos());
    }

    public function sendMessage(Request $request): JsonResponse
    {
        $request_token = $request->server('HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN');
        $origin_token = env('HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN');
        if ($request_token == $origin_token) {
            $data = [
                'chat_id' => env('TELEGRAM_ADMIN_USER_ID'),
                'text' => $request->post('text'),
            ];
            $this->dispatch(new SendMessageJob($data, null, 0));
            return $this->json([
                'code' => 0,
                'msg' => 'success',
                'ok' => true,
                'result' => true,
                'description' => 'success',
            ]);
        } else {
            return $this->json([
                'code' => -1,
                'msg' => 'failed',
                'ok' => false,
                'result' => false,
                'description' => 'Secret token invalid.',
            ]);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws TelegramException
     */
    public function webhook(Request $request): JsonResponse
    {
        $request_token = $request->server('HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN');
        $origin_token = env('HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN');
        if ($request_token == $origin_token) {
            $telegram = BotCommon::getTelegram();
            $update = new Update($request->all(), $telegram->getBotUsername());
            $updateId = $update->getUpdateId();
            $now = Carbon::createFromTimestamp(LARAVEL_START);
            $clientIP = IP::getClientIp();
            $expireTime = Carbon::now()->addMinutes(5);
            Cache::put("TelegramUpdateStartTime_$updateId", $now->getTimestampMs(), $expireTime);
            Cache::put("TelegramIP_$updateId", $clientIP, $expireTime);
            $this->dispatch(new WebhookJob($update, $telegram, $updateId));
            return $this->json([
                'code' => 0,
                'msg' => 'success',
                'ok' => true,
                'result' => true,
                'description' => 'success',
            ]);
        } else {
            return $this->json([
                'code' => -1,
                'msg' => 'failed',
                'ok' => false,
                'result' => false,
                'description' => 'Secret token invalid.',
            ]);
        }
    }
}
