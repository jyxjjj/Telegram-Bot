<?php

namespace App\Services;

use App\Services\Base\BaseService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Telegram;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use Throwable;

class CallbackQueryHandleService extends BaseService
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
        $callbackQuery = $update->getCallbackQuery();
        $files = new RegexIterator(
            new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(app_path('Services/Callbacks'))
            ),
            '/^.+Callback.php$/'
        );
        foreach ($files as $file) {
            $fileName = $file->getFileName();
            $callback = str_replace('.php', '', $fileName);
            $callback_class = "App\\Services\\Callbacks\\$callback";
            try {
                $callback_class = app()->make($callback_class);
            } catch (Throwable) {
                continue;
            }
            $callback_class->handle($callbackQuery, $telegram, $updateId);
        }
    }
}
