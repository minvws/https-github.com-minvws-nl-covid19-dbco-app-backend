<?php

declare(strict_types=1);

use App\Http\Controllers\StatusController;
use Illuminate\Support\Facades\Route;

// Liveness check for k8s
Route::get('/ping', [StatusController::class, 'ping']);
Route::get('/status', [StatusController::class, 'status']); // Route is public for the monitoring but blocked by firewall so not really public.
