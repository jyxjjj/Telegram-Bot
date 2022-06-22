<?php

namespace App\Http\Controllers;

use App\Common\DESMG;
use Illuminate\Http\JsonResponse;

class IndexController extends BaseController
{
    public function index(): JsonResponse
    {
        dd(DESMG::about([], 123));
        dd(request()->server('HTTP_CF_CONNECTING_IP'), request()->ip());
    }
}
