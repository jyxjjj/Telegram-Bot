<?php

use Illuminate\Support\Facades\Route;

Route::group(
    [
    ],
    function () {
        Route::get('/', 'IndexController@index');
    }
);

Route::group(
    [
        'prefix' => 'api',
    ],
    function () {
        Route::post("/webhook", 'IndexController@webhook');
    }
);
