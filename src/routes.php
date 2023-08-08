<?php

use Illuminate\Support\Facades\Route;
use Flamix\Health\Controllers\HealthController;

Route::get('/health/status.{response}', [HealthController::class, 'show'])->middelware(['api', 'throttle:600,1']);