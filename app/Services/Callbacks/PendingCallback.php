<?php

namespace App\Services\Callbacks;

use App\Jobs\AnswerCallbackQueryJob;
use App\Jobs\EditMessageReplyMarkupJob;
use App\Jobs\IgnorePendingJob;
use App\Jobs\PassPendingJob;
use App\Jobs\RejectPendingJob;
use App\Services\Base\BaseCallback;
use Exception;
use Longman\TelegramBot\Entities\CallbackQuery;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Longman\TelegramBot\Telegram;

class PendingCallback extends BaseCallback
{
    /**
     * @param CallbackQuery $message
     * @param Telegram $telegram
     * @param int $updateId
     * @return void
     * @throws Exception
     */
    public function handle(CallbackQuery $message, Telegram $telegram, int $updateId): void
    {
        $callbackQueryId = $message->getId();
        $data = [
            'callback_query_id' => $callbackQueryId,
            'text' => '',
            'show_alert' => false,
        ];
        $isSelfSent = $telegram->getBotId() === $message->getMessage()->getFrom()->getId();
        if (!$isSelfSent) {
            $data['text'] = '本Bot不会处理来自其他Bot或转发消息的回调请求';
            $this->dispatch(new AnswerCallbackQueryJob($data));
            return;
        }
        $callbackData = $message->getData();
        $chatId = $message->getMessage()->getChat()->getId();
        $messageId = $message->getMessage()->getMessageId();
        $fromNickname = ($message->getFrom()->getFirstName() ?? '') . ($message->getFrom()->getLastName() ?? '');
        if (preg_match('/(pendingpass|pendingreject|pendingignore)(.{16})/', $callbackData, $matches)) {
            $replyMarkupKeyboard = new InlineKeyboard([]);
            $cvid = $matches[2];
            switch ($matches[1]) {
                case 'pendingpass':
                    $data['text'] = '已通过，投稿ID:' . $cvid;
                    $replyMarkupKeyboard->addRow(
                        new InlineKeyboardButton([
                            'text' => "✅ 已通过 by $fromNickname",
                            'callback_data' => "endedhandle$cvid",
                        ]),
                    );
                    $this->dispatch(new PassPendingJob($cvid));
                    break;
                case 'pendingreject':
                    $data['text'] = '已拒绝，投稿ID:' . $cvid;
                    $replyMarkupKeyboard->addRow(
                        new InlineKeyboardButton([
                            'text' => "❌ 已拒绝 by $fromNickname",
                            'callback_data' => "endedhandle$cvid",
                        ]),
                    );
                    $this->dispatch(new RejectPendingJob($cvid));
                    break;
                case 'pendingignore':
                    $data['text'] = '已忽略，投稿ID:' . $cvid;
                    $replyMarkupKeyboard->addRow(
                        new InlineKeyboardButton([
                            'text' => "🗑 已忽略 by $fromNickname",
                            'callback_data' => "endedhandle$cvid",
                        ]),
                    );
                    $this->dispatch(new IgnorePendingJob($cvid));
                    break;
            }
            $this->dispatch(new AnswerCallbackQueryJob($data));
            $replyMarkupKeyboardMessage = [
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'reply_markup' => $replyMarkupKeyboard,
            ];
            $this->dispatch(new EditMessageReplyMarkupJob($replyMarkupKeyboardMessage));
        }
        $data['text'] = '你想干嘛？你什么目的？';
        $this->dispatch(new AnswerCallbackQueryJob($data));
    }
}
