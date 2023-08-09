<?php

namespace Flamix\Health;

use Illuminate\Support\ServiceProvider;
use Spatie\Health\Facades\Health;

class HealthProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/routes.php');

        // All health from config
        if ($this->app->runningInConsole()) {
            $checks_config = config('health.checks', []);

            foreach ($checks_config as $class => &$params) {
                // Dynamicly add params if exist
                if (!empty($params) && is_array($params)) {
                    $obj = call_user_func([$class, 'new']);
                    foreach ($params as $func => $param) {
                        // If we sever times call same functions to check
                        if (is_numeric($func)) {
                            $obj = call_user_func([$class, 'new']); // ReInit every time
                            foreach ($param as $inner_func => $inner_params) {
                                $obj = call_user_func([$obj, $inner_func], $inner_params);
                            }
                        } else {
                            $obj = call_user_func([$obj, $func], $param);
                        }
                    }

                    $checks[] = $obj;
                    continue;
                }

                $checks[] = call_user_func([$params, 'new']);
            }

            if (!empty($checks ?? [])) {
                Health::checks($checks);
            }
        }
    }
}
