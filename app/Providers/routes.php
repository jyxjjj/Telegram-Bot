<?php

use App\Http\Controllers\IndexController;
use App\Http\Controllers\Telegraph;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

Route::group(
    [
    ],
    function () {
        Route::get('/', [IndexController::class, 'index']);
        Route::get('/generate_204', fn() => response(null, Response::HTTP_NO_CONTENT)); // Captive Portal Detection
        Route::get('tf/{type}/{file}', [Telegraph::class, 'getFile']); // Telegraph File
    }
);

Route::group(
    [
        'prefix' => 'api',
    ],
    function () {
        Route::post("/webhook", [IndexController::class, 'webhook']);
        Route::post("/sendMessage", [IndexController::class, 'sendMessage']);
    }
);
