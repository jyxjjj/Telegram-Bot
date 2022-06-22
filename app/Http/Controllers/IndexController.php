<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class IndexController extends BaseController
{
    public function index(): JsonResponse
    {
        dd(request()->server('HTTP_CF_CONNECTING_IP'), request()->ip());
    }
}
