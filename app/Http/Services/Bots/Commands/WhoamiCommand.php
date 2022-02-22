<?php

namespace App\Http\Services\Bots\Commands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;

class WhoamiCommand extends UserCommand
{
    protected $name = 'whoami';
    protected $description = 'Show your id, name and username';
    protected $usage = '/whoami';
    protected $version = '1.0.0';
    protected $private_only = false;

    public function execute(): ServerResponse
    {
        dispatch(new WhoamiCommandHanlder($this->getMessage()));
        return Request::emptyResponse();
    }
}
