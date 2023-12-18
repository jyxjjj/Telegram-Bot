<?php

namespace App\Console\Schedule\WellKnownSoftwareUpdateSubscribe;

use JetBrains\PhpStorm\ArrayShape;
use Longman\TelegramBot\Entities\InlineKeyboard;

interface SoftwareInterface
{
    /**
     * @param int $chat_id
     * @param string $version
     * @return array
     */
    #[ArrayShape([
        'chat_id' => 'int',
        'text' => 'string',
        'reply_markup' => InlineKeyboard::class,
    ])]
    public function generateMessage(int $chat_id, string $version): array;

    /**
     * @return string
     */
    public function getVersion(): string;
}
