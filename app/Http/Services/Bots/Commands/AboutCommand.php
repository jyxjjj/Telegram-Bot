<?php

namespace App\Http\Services\Bots\Commands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;

class AboutCommand extends UserCommand
{
    protected $name = 'about';
    protected $description = 'About info';
    protected $usage = '/about';
    protected $version = '1.0.0';
    protected $private_only = true;

    public function execute(): ServerResponse
    {
        dispatch(new AboutCommandHandler($this->getMessage()->getChat()->getId()));
        return Request::emptyResponse();
    }
}
