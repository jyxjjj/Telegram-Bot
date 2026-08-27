<?php
/**
 * DESMG Telegram Bot
 * This file is a part of our Open Source Project (https://github.com/jyxjjj/Telegram-Bot)
 *
 * @copyright 2015-2025 DESMG
 * @license GNU Affero General Public License v3.0 (https://www.gnu.org/licenses/agpl-3.0.html)
 * @author DESMG (www.desmg.com) < opensource@desmg.org >
 *
 * @QQ 773933146
 * @Telegram jyxjjj (https://t.me/jyxjjj)
 * @Producer DESMG
 *
 * Copyright (C) 2015-2025 DESMG
 * All Rights Reserved.
 *
 * Released under GNU Affero General Public License Version 3.0
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, version 3 of the License.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Services;

use App\Services\Base\BaseService;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Telegram;

class ChatMemberHandleService extends BaseService
{
    /**
     * @param Update $update
     * @param Telegram $telegram
     * @param int $updateId
     * @return void
     */
    public function handle(Update $update, Telegram $telegram, int $updateId): void
    {
        $chatMemberUpdated = $update->getChatMember();
        $chat = $chatMemberUpdated->getChat();
        $chatType = $chat->getType();
        if ($chatType != 'group' && $chatType != 'supergroup') {
            return;
        }

        $oldChatMember = $chatMemberUpdated->getOldChatMember();
        $newChatMember = $chatMemberUpdated->getNewChatMember();

        $wasMember = match ($oldChatMember->getStatus()) {
            'creator', 'administrator', 'member' => true,
            'restricted' => $oldChatMember->getIsMember(),
            'left', 'kicked' => false,
        };
        $isMember = match ($newChatMember->getStatus()) {
            'creator', 'administrator', 'member' => true,
            'restricted' => $newChatMember->getIsMember(),
            'left', 'kicked' => false,
        };

        if ($wasMember || !$isMember) {
            return;
        }

        $user = $newChatMember->getUser();
        if ($user->getIsBot()) {
            return;
        }

        $chatId = $chat->getId();
        $userId = $user->getId();
        $firstName = $user->getFirstName();
        $lastName = $user->getLastName();
    }
}
