<?php

namespace Flamix\Health\Checks;

use Illuminate\Support\Str;
use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;

/**
 * If we use Redis in queue.
 */
class QueueRedisCheck extends Check
{
    public function run(): Result
    {
        $result = Result::make();
        $driver = config('queue.default');
        if ($driver !== 'redis') {
            return $result->failed("Set cache to redis! Now you use {$driver}.");
        }

        $redis_prefix = config('database.redis.options.prefix');
        $redis_prefix_default = Str::slug(config('app.name', 'laravel'), '_').'_database_';

        if ($redis_prefix === $redis_prefix_default) {
            return $result->warning("Do not use default redis prefix (Queue issue)! Set REDIS_PREFIX in .env!");
        }

        return $result->ok('Redis OK');
    }
}
