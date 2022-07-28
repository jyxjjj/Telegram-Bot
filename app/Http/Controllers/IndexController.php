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
    /**
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        $clientIP = $request->server('HTTP_CF_CONNECTING_IP');
        $clientCountry = $request->server('HTTP_CF_IPCOUNTRY');
        return $this->plain("$clientIP ($clientCountry)");
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws TelegramException
     */
    public function webhook(Request $request): JsonResponse
    {
        $request_token = $request->server('HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN');
        $origin_token = env('HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN');
        if ($request_token == $origin_token) {
            $telegram = BotCommon::getTelegram();
            $update = new Update($request->all(), $telegram->getBotUsername());
            $updateId = $update->getUpdateId();
            $now = Carbon::createFromTimestamp(LARAVEL_START);
            $clientIP = $request->server('HTTP_CF_CONNECTING_IP');
            $clientCountry = $request->server('HTTP_CF_IPCOUNTRY');
            $expireTime = Carbon::now()->addMinutes(5);
            Cache::put("TelegramUpdateStartTime_$updateId", $now->getTimestampMs(), $expireTime);
            Cache::put("TelegramIP_$updateId", $clientIP, $expireTime);
            Cache::put("TelegramIPCOUNTRY_$updateId", $clientCountry, $expireTime);
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
