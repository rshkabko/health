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
            $checks = config('health.checks', []);

            if (!empty($checks)) {
                Health::checks($checks);
            }
        }
    }
}
