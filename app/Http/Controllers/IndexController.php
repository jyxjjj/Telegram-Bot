<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
        Log::debug('webhook', [$request->all(), $request->server('HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN')]);
    }
}
