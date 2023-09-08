<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::withoutMiddleware('audit.requests')->group(static function () {
    Route::get('/', static function () {
        return redirect('version');
    });

    Route::get('version', static function () {
        return 'v1.0.0';
    });
});
