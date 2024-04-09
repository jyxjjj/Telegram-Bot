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

namespace App\Jobs;

use App\Common\ERR;
use App\Jobs\Base\BaseQueue;
use App\Services\UpdateHandleService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Telegram;

class WebhookJob extends BaseQueue
{
    private Update $update;
    private Telegram $telegram;
    private int $updateId;

    /**
     * @param Update $update
     * @param Telegram $telegram
     * @param int $updateId
     */
    public function __construct(Update $update, Telegram $telegram, int $updateId)
    {
        parent::__construct();
        $this->update = $update;
        $this->telegram = $telegram;
        $this->updateId = $updateId;
    }

    public function handle(): void
    {
        $update = $this->update;
        $telegram = $this->telegram;
        $updateId = $this->updateId;
        try {
            /** @var UpdateHandleService $service */
            $service = app(UpdateHandleService::class);
            $service->handle($update, $telegram, $updateId);
        } catch (TelegramException|BindingResolutionException $e) {
            ERR::log($e);
        }
    }
}
