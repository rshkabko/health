## Flamix Health Checks

## Install

```bash

# add to config/health.php

'checks' => [
  \Flamix\Health\Checks\MemcachedCheck::class,
  \Flamix\Health\Checks\SSLExpireCheck::class => [
      'name' => 'HelpDesk Ssl',
      'domain' => 'http://b24.flamix.info/',
  ],
  \Spatie\Health\Checks\Checks\PingCheck::class => [
    [
        'name' => 'HelpDesk',
        'url' => 'https://cp.flamix.solutions',
    ],
    [
        'name' => 'Main Site',
        'url' => 'https://en.flamix.solutions/status.php',
    ],
  ],    
]

# Run
php artisan health:check

# Add to Cron
\Flamix\Health\Controllers\HealthController::schedule($schedule);
```