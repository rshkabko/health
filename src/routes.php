<?php

use Illuminate\Support\Facades\Route;
use Flamix\Health\Controllers\HealthController;

Route::get('/health/status.{response}', [HealthController::class, 'show'])->middleware(['api', 'throttle:600,1'])->withoutMiddleware(['web']);