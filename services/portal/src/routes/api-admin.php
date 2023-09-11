<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Admin\ApiCalendarItemConfigController;
use App\Http\Controllers\Api\Admin\ApiCalendarItemConfigDateOperationController;
use App\Http\Controllers\Api\Admin\ApiCalendarItemConfigStrategyController;
use App\Http\Controllers\Api\Admin\ApiCalendarItemController;
use App\Http\Controllers\Api\Admin\ApiCalendarViewController;
use App\Http\Controllers\Api\Admin\ApiPolicyGuidelineController;
use App\Http\Controllers\Api\Admin\ApiPolicyVersionController;
use App\Http\Controllers\Api\Admin\ApiRiskProfileController;
use App\Http\Middleware\PolicyVersionStatusCheck;
use App\Http\Middleware\ValidateFilters;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'audit'])
    ->scopeBindings()
    ->whereUuid(['policy_version', 'risk_profile', 'calendar_item', 'calendar_view'])
    ->group(static function (): void {
        Route::apiResource('policy-version', ApiPolicyVersionController::class);

        Route::apiResource('policy-version.risk-profile', ApiRiskProfileController::class)
            ->only(['index', 'show', 'update'])
            ->middleware([ValidateFilters::class, PolicyVersionStatusCheck::class]);

        Route::apiResource('policy-version.policy-guideline', ApiPolicyGuidelineController::class)
            ->only(['index', 'show', 'update']);

        Route::apiResource('policy-version.calendar-item', ApiCalendarItemController::class)
            ->middleware([ValidateFilters::class, PolicyVersionStatusCheck::class]);

        Route::apiResource('policy-version.policy-guideline.calendar-item-config', ApiCalendarItemConfigController::class)
            ->only(['index', 'update'])
            ->middleware([PolicyVersionStatusCheck::class]);

        Route::apiResource(
            'policy-version.policy-guideline.calendar-item-config.calendar-item-config-strategy',
            ApiCalendarItemConfigStrategyController::class,
        )
            ->only(['update'])
            ->middleware([PolicyVersionStatusCheck::class]);

        Route::apiResource(
            'policy-version.policy-guideline.calendar-item-config.calendar-item-config-strategy.date-operation',
            ApiCalendarItemConfigDateOperationController::class,
        )
            ->only(['update'])
            ->middleware([PolicyVersionStatusCheck::class]);

        Route::apiResource('policy-version.calendar-view', ApiCalendarViewController::class)
            ->only(['index', 'show', 'update'])
            ->middleware([ValidateFilters::class, PolicyVersionStatusCheck::class]);
    });
