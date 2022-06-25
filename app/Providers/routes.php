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
        Route::get("/webhook", 'IndexController@webhook');
    }
);
