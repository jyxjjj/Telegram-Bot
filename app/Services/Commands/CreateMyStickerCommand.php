<?php

namespace App\Services\Commands;

use App\Jobs\SendMessageJob;
use App\Services\Base\BaseCommand;
use Illuminate\Support\Facades\Log;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;
use Throwable;

class CreateMyStickerCommand extends BaseCommand
{
    public string $name = 'createmysticker';
    public string $description = 'Create sticker pack';
    public string $usage = '/createmysticker';

    /**
     * @param Message $message
     * @param Telegram $telegram
     * @param int $updateId
     * @return void
     */
    public function execute(Message $message, Telegram $telegram, int $updateId): void
    {
        $messageId = $message->getMessageId();
        $chatId = $message->getChat()->getId();
        $userId = $message->getFrom()->getId();
        $data = [
            'chat_id' => $chatId,
            'reply_to_message_id' => $messageId,
            'text' => '',
        ];
        $param = $message->getText(true);
        $param = str_replace(' ', '', $param);
        if ($param == '') {
            $data['text'] .= "<b>Error</b>: You should provide a name for your sticker pack.\n";
            $this->dispatch(new SendMessageJob($data));
            return;
        }
        $stickerName = 'user_' . $userId . '_by_' . $telegram->getBotUsername();
        try {
            $serverResponse = Request::createNewStickerSet([
                'user_id' => $userId,
                'name' => $stickerName,
                'title' => $param,
                'emojis' => hex2bin('C2A9'),
                'png_sticker' => public_path('512x512.png'),
            ]);
            if ($serverResponse->isOk()) {
                $data['text'] .= "Sticker pack <b>$param</b> created successfully.\n";
                $data['text'] .= "You can add more stickers to it by using /addmysticker command.\n";
                $data['text'] .= "You may go to @Stickers to manage this stikcer pack.\n\n";
                $data['text'] .= "<b>Sticker Link</b>: <code>https://t.me/addstickers/$stickerName</code>\n";
                $data['reply_markup'] = new InlineKeyboard([]);
                $addButton = new InlineKeyboardButton([
                    'text' => 'Add This Sticker Pack',
                    'url' => "https://t.me/addstickers/$stickerName",
                ]);
                $data['reply_markup']->addRow($addButton);
                $this->dispatch(new SendMessageJob($data, null, 0));
            } else {
                if ($serverResponse->getDescription() == 'Bad Request: sticker set name is already occupied') {
                    $data['text'] .= "You already have a sticker pack.\n";
                    $data['text'] .= "<b>Sticker Link</b>: <code>https://t.me/addstickers/$stickerName</code>\n\n";
                    $data['text'] .= "If you really cannot find this sticker even via @Stickers, contact @jyxjjj.\n";
                    $data['reply_markup'] = new InlineKeyboard([]);
                    $addButton = new InlineKeyboardButton([
                        'text' => 'Add This Sticker Pack',
                        'url' => "https://t.me/addstickers/$stickerName",
                    ]);
                    $data['reply_markup']->addRow($addButton);
                    $this->dispatch(new SendMessageJob($data, null, 0));
                } else {
                    $data['text'] .= "<b>Error</b>: Sticker pack <b>$param</b> created failed.\n";
                    $data['text'] .= "<b>Error Code</b>: <code>{$serverResponse->getErrorCode()}</code>\n";
                    $data['text'] .= "<b>Error Msg</b>: <code>{$serverResponse->getDescription()}</code>\n\n";
                    $data['text'] .= "You may go to @Stickers to view if it was created successfully or already exists.\n";
                    $this->dispatch(new SendMessageJob($data));
                }
            }
        } catch (Throwable $e) {
            $data['text'] .= "<b>Error</b>: Sticker pack <b>$param</b> created failed.\n";
            $data['text'] .= "An error occurred while creating the sticker pack.\n";
            Log::error($e->getMessage(), $e->getTrace());
            $this->dispatch(new SendMessageJob($data));
        }
    }
}
