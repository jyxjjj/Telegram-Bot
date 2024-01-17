<?php

namespace App\Services\Commands;

use App\Jobs\SendMessageJob;
use App\Services\Base\BaseCommand;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

class IATACommand extends BaseCommand
{
    public string $name = 'iata';
    public string $description = 'Search iata info.';
    public string $usage = '/iata {Search String}';

    /**
     * @param Message $message
     * @param Telegram $telegram
     * @param int $updateId
     * @return void
     */
    public function execute(Message $message, Telegram $telegram, int $updateId): void
    {
        $chatId = $message->getChat()->getId();
        $messageId = $message->getMessageId();
        $params = $message->getText(true);
        $data = [
            'chat_id' => $chatId,
            'reply_to_message_id' => $messageId,
            'text' => $this->find(trim($params)),
        ];
        $this->dispatch(new SendMessageJob($data, null, 0));
    }

    private function find(string $name): string
    {
        $exists = file_exists(database_path('airports.json'));
        if (!$exists) {
            return "<b>ERROR</b>: Cannot found airports database.";
        }
        $file = file_get_contents(database_path('airports.json'));
        $json = json_decode($file, true);
        unset($file);
        foreach ($json as $id => $airport) {
            if (str_contains(strtolower($airport['iata'] ?? ''), strtolower($name))) {
                return <<<EOF
Name: {$airport['name']}
IATA: {$airport['iata']}
ICAO: {$airport['icao']}
Place: {$airport['city']}, {$airport['state']}, {$airport['country']}
Location: <a href="https://www.google.com/maps?q={$airport['lat']},{$airport['lon']}">{$airport['lat']}, {$airport['lon']}, {$airport['elevation']}</a>
Timezone: {$airport['tz']}
EOF;
            }
        }
        return "<b>ERROR</b>: Cannot found airport which iata contains '$name'.";
    }
}
