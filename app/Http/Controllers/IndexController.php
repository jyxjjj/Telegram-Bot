<?php

namespace App\Http\Controllers;

use App\Common\BotCommon;
use App\Common\IP;
use App\Jobs\SendMessageJob;
use App\Jobs\WebhookJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Exception\TelegramException;

class IndexController extends BaseController
{
    /**
     * @return Response
     */
    public function index(): Response
    {
        return $this->plain(IP::getClientIpInfos());
    }

    public function sendMessage(Request $request): JsonResponse
    {
        $request_token = $request->server('HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN');
        $origin_token = env('HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN');
        if ($request_token == $origin_token) {
            $data = [
                'chat_id' => env('TELEGRAM_ADMIN_USER_ID'),
                'text' => $request->post('text'),
            ];
            $this->dispatch(new SendMessageJob($data, null, 0));
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
            $clientIP = IP::getClientIp();
            $expireTime = Carbon::now()->addMinutes(5);
            Cache::put("TelegramUpdateStartTime_$updateId", $now->getTimestampMs(), $expireTime);
            Cache::put("TelegramIP_$updateId", $clientIP, $expireTime);
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
