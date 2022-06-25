<?php

namespace App\Http\Controllers;

use App\Common\DESMG;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class IndexController extends BaseController
{
    public function index(): JsonResponse
    {
        dd(DESMG::about([], 123));
        dd(request()->server('HTTP_CF_CONNECTING_IP'), request()->ip());
    }

    public function webhook(Request $request): JsonResponse
    {
        Log::debug('webhook', [$request->all(), $request->server('HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN')]);
        return response()->json(['status' => 'ok']);
    }
}
