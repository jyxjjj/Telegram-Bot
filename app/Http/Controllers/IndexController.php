<?php

namespace App\Http\Controllers;

use App\Common\BotCommon;
use App\Jobs\WebhookJob;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Exception\TelegramException;

class IndexController extends BaseController
{
    public function index(): Response
    {
        return $this->plain(request()->server('HTTP_CF_CONNECTING_IP') . ' (' . request()->server('HTTP_CF_IPCOUNTRY') . ')');
    }

    /**
     * @throws TelegramException
     */
    public function webhook(Request $request): JsonResponse
    {
        $request_token = $request->server('HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN');
        $origin_token = env('HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN');
        if ($request_token == $origin_token) {
            $telegram = BotCommon::getTelegram();
            $update = new Update($request->all(), $telegram->getBotUsername());
            $telegram->enableAdmin(env('TELEGRAM_ADMIN_USER_ID'));
            $telegram->setDownloadPath(storage_path('app/telegram'));
            $telegram->setUploadPath(storage_path('app/telegram'));
            $updateId = $update->getUpdateId();
            $now = Carbon::createFromTimestamp(LARAVEL_START);
            Cache::put("TelegramUpdateStartTime_$updateId", $now->getTimestampMs(), now()->addMinutes(5));
            $this->dispatch(new WebhookJob($update, $telegram, $updateId));
            return $this->json([
                'code' => 0,
                'msg' => 'success',
                'ok' => true,
                'result' => true,
                'description' => 'success',
            ]);
        } else {
            return $this->json([
                'code' => -1,
                'msg' => 'failed',
                'ok' => false,
                'result' => false,
                'description' => 'Secret token invalid.',
            ]);
        }
    }
}
