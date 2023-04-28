<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    protected $namespace = 'App\\Http\\Controllers';

    public function boot(): void
    {
        $this->routes(
            function () {
                Route::namespace($this->namespace)
                    ->group(app_path('Providers/routes.php'));
            }
        );
    }
}
