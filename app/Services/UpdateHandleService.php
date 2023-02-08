<?php

namespace App\Services;

use App\Services\Base\BaseService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Telegram;

class UpdateHandleService extends BaseService
{
    /**
     * @var array
     */
    private array $handlers = [];

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
//                 任何类型的新传入消息--文本、照片、贴纸等。
        $this->addHandler(Update::TYPE_MESSAGE, MessageHandleService::class);
//                 机器人已知并已编辑的消息的新版本。
//        $this->addHandler(Update::TYPE_EDITED_MESSAGE, EditedMessageHandleService::class);
//                 任何类型的新传入频道帖子--文字、照片、贴纸等。
        $this->addHandler(Update::TYPE_CHANNEL_POST, ChannelPostHandleService::class);
//                 机器人已知并已编辑的频道帖子的新版本。
//        $this->addHandler(Update::TYPE_EDITED_CHANNEL_POST, MessageHandleService::class);
//                 新的传入回调查询。
//        $this->addHandler(Update::TYPE_CALLBACK_QUERY, CallbackQueryHandleService::class);
        $updateType = $update->getUpdateType();
        $this->runHandler($updateType, $update, $telegram, $updateId);
    }

    /**
     * @param string $needType
     * @param string $class
     */
    private function addHandler(string $needType, string $class)
    {
        $this->handlers[] = [
            'type' => $needType,
            'class' => $class,
        ];
    }

    /**
     * @param string $type
     * @param Update $update
     * @param Telegram $telegram
     * @param int $updateId
     * @return void
     * @throws BindingResolutionException
     * @throws TelegramException
     */
    private function runHandler(string $type, Update $update, Telegram $telegram, int $updateId): void
    {
        foreach ($this->handlers as $handler) {
            if ($type == $handler['type'] || $handler['type'] == '*' || $handler['type'] == 'ANY') {
                app()->make($handler['class'])->handle($update, $telegram, $updateId);
            }
        }
    }
}
