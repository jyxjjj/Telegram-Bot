<?php

namespace App\Services\Commands;

use App\Jobs\SendMessageJob;
use App\Services\Base\BaseCommand;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

class AV2BVCommand extends BaseCommand
{
    public string $name = 'av2bv';
    public string $description = 'AV to BV';
    public string $usage = '/av2bv [AVID]';
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
        $bvid = $this->av2bv($param);
        $link = "https://b23.tv/$bvid";
        $data = [
            'chat_id' => $chatId,
            'text' => '',
            'reply_markup' => new InlineKeyboard([]),
        ];
        $data['text'] .= "BVID: <code>$bvid</code>" . PHP_EOL;
        $data['text'] .= "Link: <code>$link</code>" . PHP_EOL;
        $button = new InlineKeyboardButton(['text' => $bvid, 'url' => $link]);
        $data['reply_markup']->addRow($button);
        $this->dispatch(new SendMessageJob($data, null, 0));
    }

    private function av2bv(string $av): string
    {
        str_starts_with($av, 'av') && $av = substr($av, 2);
        if (!is_numeric($av)) return 'Invaild AV ID.';
        $temp = "BV1@@4@1@7@@";
        for ($i = 0; $i < count($this->a2bEncIndex); $i++) {
            $temp = substr($temp, 0, $this->a2bEncIndex[$i])
                . $this->a2bEncTable[floor(
                    (
                        ($av ^ $this->a2bXorEnc)
                        + $this->a2bAddEnc
                    )
                    / pow(strlen($this->a2bEncTable), $i)
                )
                % strlen($this->a2bEncTable)]
                . substr($temp, $this->a2bEncIndex[$i] + 1);
        }
        return $temp;
    }

}
