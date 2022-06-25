<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
//        DB::listen(function ($sql) {
//            foreach ($sql->bindings as &$binding) {
//                if ($binding instanceof DateTime) {
//                    $binding = $binding->format('Y-m-d H:i:s');
//                }
//                if (is_string($binding)) {
//                    $binding = "'$binding'";
//                }
//            }
//            $query = str_replace(['%', '?'], ['%%', '%s'], $sql->sql);
//            try {
//                $query = vsprintf($query, $sql->bindings);
//                Log::channel('sql')->info($query, [__FILE__, __LINE__]);
//            } catch (Exception $e) {
//                Log::channel('sql')->debug($query, [__FILE__, __LINE__]);
//                Log::channel('sql')->error('Cannot format SQL', [__FILE__, __LINE__, $e->getCode(), $e->getMessage()]);
//                ERR::log($e, __FILE__, __LINE__);
//            }
//        });
    }

    public function boot()
    {
        RateLimiter::for('TelegramLimitedApiRequest', function ($job) {
            return Limit::perMinute(20);
        });
    }
}
