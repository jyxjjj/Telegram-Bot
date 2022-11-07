<?php

namespace App\Http\Controllers;

use App\Common\Config;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class BaseController extends Controller
{
    use DispatchesJobs;

    /**
     * @param array $data
     * @return JsonResponse
     */
    final protected function json(array $data = []): JsonResponse
    {
        return response()->json($data);
    }

    /**
     * @param $str
     * @return Response
     */
    final protected function plain($str): Response
    {
        return response($str, 200, Config::PLAIN_HEADER);
    }
}
