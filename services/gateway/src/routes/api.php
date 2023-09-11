<?php

declare(strict_types=1);

use App\Http\Controllers\HeartbeatController;
use App\Http\Controllers\ReportTestResultController;
use Illuminate\Support\Facades\Route;

Route::group(
    ['prefix' => 'v1', 'middleware' => ['prometheus', 'jwt', 'audit']],
    static function (): void {
        Route::get('heartbeat', [HeartbeatController::class, '__invoke']);
        Route::post('test-results', [ReportTestResultController::class, '__invoke']);
    },
);
