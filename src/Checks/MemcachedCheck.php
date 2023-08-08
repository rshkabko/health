<?php

namespace Flamix\Health\Checks;

use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;
use Illuminate\Support\Facades\Cache;

/**
 * Checking if memcached use as main cache driver and working.
 */
class MemcachedCheck extends Check
{
    public function run(): Result
    {
        $result = Result::make();
        $driver = config('cache.default');
        if ($driver !== 'memcached') {
            return $result->failed("Set cache to memcached first! Now you use {$driver}.");
        }

        // Save test value
        Cache::put('memcached_test', true, now()->addSeconds(10));

        // If test value OK - Memcached working!
        if (Cache::get('memcached_test', false)) {
            return $result->ok();
        }

        return $result->failed("Memcached can't save test value!");
    }
}
