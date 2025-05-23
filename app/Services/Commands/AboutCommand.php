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
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Services\Commands;

use App\Common\RequestHelper;
use App\Jobs\SendMessageJob;
use App\Services\Base\BaseCommand;
use Illuminate\Http\Client\ConnectionException;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;
use Throwable;

class AboutCommand extends BaseCommand
{
    public string $name = 'about';
    public string $description = 'About';
    public string $usage = '/about';

    /**
     * @param Message $message
     * @param Telegram $telegram
     * @param int $updateId
     * @return void
     * @throws ConnectionException
     */
    public function execute(Message $message, Telegram $telegram, int $updateId): void
    {
        $chatId = $message->getChat()->getId();
        try {
            $commits = RequestHelper::getInstance()
                ->accept('application/vnd.github.v3+json')
                ->withToken(env('GITHUB_TOKEN'))
                ->get('https://api.github.com/repos/jyxjjj/Telegram-Bot/commits?per_page=1')
                ->json();
            $commits = $commits[0];
            $home = $commits['html_url'];
            $version = substr(strtoupper($commits['sha']), 0, 7);
            $version = "<a href='$home'>$version</a>";
            $date = date('Y-m-d H:i:s', strtotime($commits['commit']['committer']['date']));
        } catch (Throwable) {
            $version = 'Fetch Failed';
            $date = date('Y-m-d H:i:s', '1970-01-01 00:00:00');
        }
        $data = [
            'chat_id' => $chatId,
            'text' => '',
        ];
        $data['text'] .= "龙缘特种工业集团机器人\n";
        $data['text'] .= "DESMG Main API(DESMG)\n";
        $data['text'] .= "龙缘科技 版权所有\n";
        $data['text'] .= sprintf("Copyright (C) %s\n", date('Y'));
        $data['text'] .= "DESMG All rights reserved.\n";
        $data['text'] .= "<b>Version</b>: $version\n";
        $data['text'] .= "<b>Updated</b>: <code>$date</code>\n";
        $data['text'] .= sprintf("<b>System Time</b>: <code>%s</code>\n", date('Y-m-d H:i:s'));
        $data['text'] .= sprintf("<b>Device Name</b>: <code>%s</code>\n", php_uname('n'));
        $data['text'] .= sprintf("<b>System Version</b>: <code>%s %s %s</code>\n", php_uname('s'), php_uname('r'), php_uname('m'));
        $data['text'] .= sprintf("<b>PHP Version</b>: <code>%s %s %s</code>\n", PHP_VERSION, PHP_SAPI, PHP_OS);
        $data['text'] .= <<<GPL
<blockquote>
Released under GNU Affero General Public License Version 3.0

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License
along with this program.  If not, see <a href="https://www.gnu.org/licenses/">https://www.gnu.org/licenses/</a>.
</blockquote>
GPL;
        $data['reply_markup'] = new InlineKeyboard([]);
        $personal = new InlineKeyboardButton([
            'text' => '个人频道',
            'url' => 'https://t.me/desmg_share',
        ]);
        $contact = new InlineKeyboardButton([
            'text' => '联系我们',
            'url' => 'https://t.me/jyxjjj',
        ]);
        $data['reply_markup']->addRow($personal, $contact);
        $github = new InlineKeyboardButton([
            'text' => 'GitHub',
            'url' => 'https://github.com/jyxjjj/Telegram-Bot',
        ]);
        $website = new InlineKeyboardButton([
            'text' => '官方网站',
            'url' => 'https://www.desmg.com',
        ]);
        $data['reply_markup']->addRow($github, $website);
        $channel = new InlineKeyboardButton([
            'text' => '官方频道',
            'url' => 'https://t.me/desmg',
        ]);
        $group = new InlineKeyboardButton([
            'text' => '官方群',
            'url' => 'https://t.me/desmg_official',
        ]);
        $data['reply_markup']->addRow($channel, $group);
        $privacy = new InlineKeyboardButton([
            'text' => '隐私政策',
            'url' => 'https://www.desmg.com/policies/privacy',
        ]);
        $usage = new InlineKeyboardButton([
            'text' => '使用条款',
            'url' => 'https://www.desmg.com/policies/terms',
        ]);
        $data['reply_markup']->addRow($privacy, $usage);
        $this->dispatch(new SendMessageJob($data));
    }
}
