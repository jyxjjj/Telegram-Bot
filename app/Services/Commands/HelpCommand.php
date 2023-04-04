<?php

namespace App\Services\Commands;

use App\Common\RequestService;
use App\Services\Base\BaseCommand;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\KeyboardButton;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

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
        $chatId = $message->getChat()->getId();
        $userId = $message->getFrom()->getId();
        $data = [
            'chat_id' => $chatId,
            'text' => '',
        ];
        $data['text'] .= "你的用户ID： $userId";
        $data['reply_markup'] = new Keyboard([]);
        $data['reply_markup']->setResizeKeyboard(true);
        $data['reply_markup']->addRow(new KeyboardButton('一步投稿'), new KeyboardButton('分步投稿'));
        $data['reply_markup']->addRow(new KeyboardButton('DMCA Request'), new KeyboardButton('版权反馈'));
        $data['reply_markup']->addRow(new KeyboardButton('客服帮助'), new KeyboardButton('技术支持'));
        $data['reply_markup']->addRow(new KeyboardButton('意见建议'), new KeyboardButton('捐赠信息'));
        $data['text'] && RequestService::getInstance()->sendMessage($data);
    }

}
