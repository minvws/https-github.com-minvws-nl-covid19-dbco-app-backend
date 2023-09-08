<?php

declare(strict_types=1);

use App\Models\Eloquent\EloquentTask;
use App\Models\Eloquent\TestResult;

return [
    'log_threshold' => [
        TestResult::class => 100,
        EloquentTask::class => 100,
    ],
];
