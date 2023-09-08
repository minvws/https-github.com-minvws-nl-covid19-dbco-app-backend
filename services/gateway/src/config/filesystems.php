<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been setup for each driver as an example of the required options.
    |
    | Supported Drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [
        'audit_event_schema' => [
            'driver' => 'local',
            'root' => storage_path('audit/schema'),
            'visibility' => 'private',
        ],

        'audit_event_specification' => [
            'driver' => 'local',
            'root' => storage_path('audit/specification'),
            'visibility' => 'private',
        ],
    ]
];
