<?php

namespace App\Services\Commands;

use App\Common\BotCommon;
use App\Jobs\SendMessageJob;
use App\Services\Base\BaseCommand;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

class HelpCommand extends BaseCommand
{
    public string $name = 'help';
    public string $description = 'Show commands list';
    public string $usage = '/help';

    /**
     * @param Message $message
     * @param Telegram $telegram
     * @param int $updateId
     * @return void
     */
    public function execute(Message $message, Telegram $telegram, int $updateId): void
    {
        $chatId = BotCommon::getChatId($message);
        $data = [
            'chat_id' => $chatId,
            'text' => $this->getHelp(),
        ];
        $this->dispatch(new SendMessageJob($data, null, 0));
    }

    /**
     * @return string
     */
    private function getHelp(): string
    {
        $path = app_path('Services/Commands');
        $files = new RegexIterator(
            new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path)
            ),
            '/^.+Command.php$/'
        );
        $help = [];
        foreach ($files as $file) {
            $fileName = $file->getFileName();
            $pathName = $file->getPathName();
            $command = str_replace('.php', '', $fileName);
            $command_class = "App\\Services\\Commands\\$command";
            require_once $pathName;
            if (!class_exists($command_class, false)) {
                continue;
            }
            $command_class = new $command_class; // instantiate the command
            $command_class->name == 'start' || $help[] = "$command_class->usage - $command_class->description";
        }
        sort($help);
        return implode("\n", $help);
    }
}
