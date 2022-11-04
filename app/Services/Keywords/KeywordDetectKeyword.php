<?php

namespace App\Services\Keywords;

use App\Models\TChatKeywords;
use App\Services\Base\BaseKeyword;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

class KeywordDetectKeyword extends BaseKeyword
{
    public string $name = 'Keyword Detecter';
    public string $description = 'Match Keywords';
    protected string $pattern = '//';

    public function preExecute(Message $message): bool
    {
        return true;
    }

    public function execute(Message $message, Telegram $telegram, int $updateId): void
    {
        $keywords = TChatKeywords::getKeywords($message->getChat()->getId());
        foreach ($keywords as $keyword) {
            $this->handle(
                $keyword['keyword'],
                $keyword['target'],
                $keyword['operation'],
                $keyword['data'],
                $message,
                $telegram,
                $updateId
            );
        }
    }

    private function handle(string $keyword, string $target, string $operation, array $data, Message $message, Telegram $telegram, int $updateId)
    {

    }
}
