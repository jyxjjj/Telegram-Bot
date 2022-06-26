<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class BaseController extends Controller
{
    use DispatchesJobs;

    final protected function json(array $data = []): JsonResponse
    {
        return response()->json($data);
    }
}
