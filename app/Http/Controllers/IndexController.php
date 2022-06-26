<?php

namespace App\Http\Controllers;

use App\Jobs\WebhookJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class IndexController extends BaseController
{
    public function index(): Response
    {
        return $this->plain(request()->server('HTTP_CF_CONNECTING_IP') . ' (' . request()->server('HTTP_CF_IPCOUNTRY') . ')');
    }

    public function webhook(Request $request): JsonResponse
    {
        $request_token = $request->server('HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN');
        $origin_token = env('HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN');
        if ($request_token == $origin_token) {
            $this->dispatch(new WebhookJob($request->all()));
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
