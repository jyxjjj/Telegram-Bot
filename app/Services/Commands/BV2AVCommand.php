<?php

namespace App\Services\Commands;

use App\Jobs\SendMessageJob;
use App\Services\Base\BaseCommand;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

class BV2AVCommand extends BaseCommand
{
    public string $name = 'bv2av';
    public string $description = 'BV to AV';
    public string $usage = '/bv2av {BVID}';
    private int $a2bAddEnc = 8728348608;
    private int $a2bXorEnc = 0b1010100100111011001100100100;
    private array $a2bEncIndex = [11, 10, 3, 8, 4, 6];
    private string $a2bEncTable = "fZodR9XQDSUm21yCkr6zBqiveYah8bt4xsWpHnJE7jL5VG3guMTKNPAwcF";

    /**
     * @param Message $message
     * @param Telegram $telegram
     * @param int $updateId
     * @return void
     */
    public function execute(Message $message, Telegram $telegram, int $updateId): void
    {
        $chatId = $message->getChat()->getId();
        $param = $message->getText(true);
        $avid = $this->bv2av($param);
        $link = "https://b23.tv/$avid";
        $data = [
            'chat_id' => $chatId,
            'text' => '',
            'reply_markup' => new InlineKeyboard([]),
        ];
        $data['text'] .= "AVID: <code>$avid</code>" . PHP_EOL;
        $data['text'] .= "Link: <code>$link</code>" . PHP_EOL;
        $button = new InlineKeyboardButton(['text' => $avid, 'url' => $link]);
        $data['reply_markup']->addRow($button);
        $this->dispatch(new SendMessageJob($data, null, 0));
    }

    private function bv2av(string $bv): string
    {
        $temp = 0;
        for ($i = 0; $i < count($this->a2bEncIndex); $i++) {
            if (!str_contains($this->a2bEncTable, $bv[$this->a2bEncIndex[$i]])) {
                return 'Invaild BV ID.';
            } else {
                $temp += strpos($this->a2bEncTable, $bv[$this->a2bEncIndex[$i]]) * pow(strlen($this->a2bEncTable), $i);
            }
        }
        $temp = $temp - $this->a2bAddEnc ^ $this->a2bXorEnc;
        return 'av' . $temp;
    }
}
