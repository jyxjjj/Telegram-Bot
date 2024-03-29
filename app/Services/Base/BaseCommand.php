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

namespace App\Services\Base;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

abstract class BaseCommand
{
    use DispatchesJobs;

    /**
     * @var string $name The name of the command
     */
    public string $name;

    /**
     * @var string $description Description of the command
     */
    public string $description;

    /**
     * @var string $usage Example: /command [parameter]
     */
    public string $usage;

    /**
     * @var string $version Version of the command
     */
    public string $version = '1.0.0';

    /**
     * @var bool $admin Need admin permission to execute
     */
    public bool $admin = false;

    /**
     * @var bool $private Private messages only
     */
    public bool $private = false;

    /**
     * @param Message $message
     * @param Telegram $telegram
     * @param int $updateId
     * @return void
     */
    public abstract function execute(Message $message, Telegram $telegram, int $updateId): void;
}
