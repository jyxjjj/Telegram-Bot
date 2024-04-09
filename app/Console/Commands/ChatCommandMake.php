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

use Illuminate\Console\Command;

class ChatCommandMake extends Command
{
    protected $signature = 'make:chat-command
        {filename : File Name}
        {commandname : Command Name}
        {description : Description}
        {usage : Usage}
        {--a|admin=false : Admin Only}
        {--p|private=false : Is Private Command}';
    protected $description = 'Generate A new chat command for group or private';

    public function handle(): int
    {
        $contents = file_get_contents(app_path('Services/Commands/StubCommand.stub'));
        $filename = $this->argument('filename');
        $commandname = $this->argument('commandname');
        $description = $this->argument('description');
        $usage = $this->argument('usage');
        $admin = $this->option('admin');
        $private = $this->option('private');
        $contents = str_replace('class StubCommand extends BaseCommand', "class $filename extends BaseCommand", $contents);
        $contents = str_replace('public string $name = \'{{NAME}}\';', "public string \$name = '$commandname';", $contents);
        $contents = str_replace('public string $description = \'{{DESCRIPTION}}\';', "public string \$description = '$description';", $contents);
        $contents = str_replace('public string $usage = \'/{{USAGE}}\';', "public string \$usage = '/$usage';", $contents);
        $contents = str_replace('public bool $admin = false;', "public bool \$admin = $admin;", $contents);
        $contents = str_replace('public bool $private = false;', "public bool \$private = $private;", $contents);
        file_put_contents(app_path("Services/Commands/$filename.php"), $contents);
        return self::SUCCESS;
    }
}
