## Flamix Health Checks

## Install

```bash

# add to config/health.php

'checks' => [
  \Spatie\Health\Checks\Checks\ScheduleCheck::new(),
  \Spatie\Health\Checks\Checks\UsedDiskSpaceCheck::new(),
  \Spatie\Health\Checks\Checks\CacheCheck::new(),
  \Spatie\Health\Checks\Checks\DebugModeCheck::new(),
  \Spatie\Health\Checks\Checks\RedisCheck::new(),
  \Flamix\Health\Checks\MemcachedCheck::new(),
  \Flamix\Health\Checks\QueueRedisCheck::new(),
]

# Run
php artisan health:check

# Add to Cron
\Flamix\Health\Controllers\HealthController::schedule($schedule);
```