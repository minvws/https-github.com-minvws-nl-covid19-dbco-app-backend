<?php

declare(strict_types=1);

return [
    'created_archived_days_in_past' => env('CASE_METRICS_CREATED_ARCHIVED_DAYS_IN_PAST', 21),
    'refresh_lock_expiry' => env('CASE_METRICS_REFRESH_LOCK_EXPIRY', 600),
    'archived_count_case_uuid_batch_size' => env('CASE_METRICS_ARCHIVED_COUNT_CASE_UUID_BATCH_SIZE', 500),

    'queue' => [
        'connection' => env('CASE_METRICS_REFRESH_JOB_CONNECTION', 'rabbitmq'),
        'queue_name' => env('CASE_METRICS_REFRESH_JOB_QUEUE_NAME', 'default'),
    ],
];
