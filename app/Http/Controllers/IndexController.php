<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IndexController extends BaseController
{
    public function index(): JsonResponse
    {
        return response()->json(
            [
                'IP' => request()->server('HTTP_CF_CONNECTING_IP'),
            ],
        );
    }

    public function webhook(Request $request): void
    {
        $request_token = $request->server('HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN');
        $origin_token = env('HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN');
        if ($request_token == $origin_token) {
            $this->json([
                'code' => 0,
                'msg' => 'success',
                'ok' => true,
                'result' => true,
                'description' => 'success',
            ]);
        }
    }
}
