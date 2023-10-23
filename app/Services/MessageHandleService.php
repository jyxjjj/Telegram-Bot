<?php

namespace App\Services;

use App\Jobs\SendMessageJob;
use App\Services\Base\BaseService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Log;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Telegram;

class MessageHandleService extends BaseService
{
    /**
     * @var array
     */
    private array $handlers;

    /**
     * @param Update $update
     * @param Telegram $telegram
     * @param int $updateId
     * @return void
     * @throws TelegramException
     * @throws BindingResolutionException
     */
    public function handle(Update $update, Telegram $telegram, int $updateId): void
    {
        $message = $update->getMessage();
        if ($this->detectWhiteList($message)) {
            return;
        }
        $messageType = $message->getType();
        $this->addHandler('ANY', KeywordHandleService::class);
        $this->addHandler('command', CommandHandleService::class);
        $this->addHandler('sticker', StickerHandleService::class);
        $this->runHandler($messageType, $message, $telegram, $updateId);
//            'command':
//            'text':
//            'audio':
//            'animation':
//            'document':
//            'game':
//            'photo':
//            'sticker':
//            'video':
//            'voice':
//            'video_note':
//            'contact':
//            'location':
//            'venue':
//            'poll':
//            'dice':
//            'new_chat_members':
//            'left_chat_member':
//            'new_chat_title':
//            'new_chat_photo':
//            'delete_chat_photo':
//            'group_chat_created':
//            'supergroup_chat_created':
//            'channel_chat_created':
//            'message_auto_delete_timer_changed':
//            'migrate_to_chat_id':
//            'migrate_from_chat_id':
//            'pinned_message':
//            'invoice':
//            'successful_payment':
//            'passport_data':
//            'proximity_alert_triggered':
//            'forum_topic_created':
//            'forum_topic_closed':
//            'forum_topic_reopened':
//            'voice_chat_scheduled':
//            'voice_chat_started':
//            'voice_chat_ended':
//            'voice_chat_participants_invited':
//            'web_app_data':
//            'reply_markup':
    }

    private function detectWhiteList(Message $message): bool
    {
        if (!in_array($message->getChat()->getId(), [886776929, -1001344643532, -1001391154172, -1001743989979,])) {
            if ($message->getChat()->getType() == 'private') {
                $str = sprintf(
                    <<<EOF
Chat Type: %s
Chat Username: %s
Chat ID: %s
Chat Name: %s%s%s

From Username: %s
From ID: %s
From Name: %s%s

MSG Type: %s

MSG:
%s
EOF,
                    $message->getChat() ? $message->getChat()->getType() ?? 'ERR::CHAT_TYPE' : 'NOCHAT',
                    $message->getChat() ? $message->getChat()->getUsername() ?? 'ERR::USERNAME' : 'NOCHAT',
                    $message->getChat() ? $message->getChat()->getId() ?? 'ERR::ID' : 'NOCHAT',
                    $message->getChat() ? $message->getChat()->getTitle() ?? 'ERR::TITLE' : 'NOCHAT',
                    $message->getChat() ? $message->getChat()->getFirstName() ?? 'ERR::FN' : 'NOCHAT',
                    $message->getChat() ? $message->getChat()->getLastName() ?? 'ERR::LN' : 'NOCHAT',
                    $message->getFrom() ? $message->getFrom()->getUsername() ?? 'ERR::USERNAME' : 'NOFROM',
                    $message->getFrom() ? $message->getFrom()->getId() ?? 'ERR::ID' : 'NOFROM',
                    $message->getFrom() ? $message->getFrom()->getFirstName() ?? 'ERR::FN' : 'NOFROM',
                    $message->getFrom() ? $message->getFrom()->getLastName() ?? 'ERR::LN' : 'NOFROM',
                    $message->getType() ?? 'ERR::MSGTYPE',
                    $message->getText() ?? $message->getCaption() ?? 'ERR::MSGTEXT',
                );
                $this->dispatch(
                    new SendMessageJob(
                        data: [
                            'chat_id' => env('TELEGRAM_ADMIN_USER_ID'),
                            'parse_mode' => '',
                            'text' => $str,
                        ],
                        delete: 300
                    )
                );
                if (in_array($message->getChat()->getId(), [296672714, 1891466551, 447632604, 5738737040, 1583896650])) {
                    $this->dispatch(
                        new SendMessageJob(
                            data: [
                                'chat_id' => $message->getChat()->getId(),
                                'parse_mode' => '',
                                'text' => 'You have been blocked',
                            ],
                            delete: 0
                        )
                    );
                    return true;
                }
                Log::debug(
                    'Chat',
                    [
                        $str
                    ]
                );
                return false;
            }
            return !($message->getType() == 'command' && $message->getCommand() == 'about');
        }
        return false;
    }

    /**
     * @param string $needType
     * @param string $class
     */
    private function addHandler(string $needType, string $class): void
    {
        $this->handlers[] = [
            'type' => $needType,
            'class' => $class,
        ];
    }

    /**
     * @param string $type
     * @param Message $message
     * @param Telegram $telegram
     * @param int $updateId
     * @return void
     * @throws BindingResolutionException
     * @throws TelegramException
     */
    private function runHandler(string $type, Message $message, Telegram $telegram, int $updateId): void
    {
        foreach ($this->handlers as $handler) {
            if ($type == $handler['type'] || $handler['type'] == '*' || $handler['type'] == 'ANY') {
                $handled = app()->make($handler['class'])->handle($message, $telegram, $updateId);
                if ($handled) {
                    return;
                }
            }
        }
    }
}
