<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
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
//            } catch (Throwable $e) {
//                Log::channel('sql')->debug($query, [__FILE__, __LINE__]);
//                Log::channel('sql')->error('Cannot format SQL', [__FILE__, __LINE__, $e->getCode(), $e->getMessage()]);
//                ERR::log($e, __FILE__, __LINE__);
//            }
//        });
    }

    public function boot()
    {
        Schema::defaultStringLength(128);
    }
}
