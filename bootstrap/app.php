<?php

use App\Console\Kernel as ConsoleKernel;
use App\Exceptions\Handler as ExHandler;
use App\Http\Kernel as HttpKernel;
use Illuminate\Contracts\Console\Kernel as IConsoleKernel;
use Illuminate\Contracts\Debug\ExceptionHandler as IExHandler;
use Illuminate\Contracts\Http\Kernel as IHttpKernel;
use Illuminate\Foundation\Application;

$app = new Application(
    $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__)
);
$app->singleton(
    IHttpKernel::class,
    HttpKernel::class
);
$app->singleton(
    IConsoleKernel::class,
    ConsoleKernel::class
);
$app->singleton(
    IExHandler::class,
    ExHandler::class
);
return $app;
