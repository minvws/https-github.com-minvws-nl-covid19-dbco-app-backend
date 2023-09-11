<?php

declare(strict_types=1);

use Monolog\Formatter\JsonFormatter;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\TestHandler;
use Monolog\Processor\PsrLogMessageProcessor;

$defaultLogChannel = env('LOG_CHANNEL', 'stderr') ?? 'null';

return [
    'default' => $defaultLogChannel,

    'deprecations' => [
        'channel' => env('LOG_CHANNEL', 'stderr'),
        'trace' => false,
    ],

    'channels' => [
        'stack' => [
            'driver' => 'stack',
            'channels' => [$defaultLogChannel],
            'ignore_exceptions' => false,
        ],

        'single' => [
            'driver' => 'stack',
            'channels' => [$defaultLogChannel],
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
        ],

        'daily' => [
            'driver' => 'monolog',
            'handler' => NullHandler::class,
            'replace_placeholders' => true,
        ],

        'stderr' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => StreamHandler::class,
            'formatter' => env('LOG_FORMAT', '') === 'json' ? JsonFormatter::class : LineFormatter::class,
            'formatter_with' => [
                'dateFormat' => DateTimeInterface::ATOM,
                'includeStacktraces' => env('LOG_INCLUDE_STACKTRACES', true),
            ],
            'with' => [
                'stream' => 'php://stderr',
            ],
            'processors' => [PsrLogMessageProcessor::class],
        ],

        'null' => [
            'driver' => 'monolog',
            'handler' => NullHandler::class,
        ],

        'emergency' => [
            'path' => '/tmp/laravel.log',
        ],

        'test' => [
            'driver' => 'monolog',
            'handler' => TestHandler::class,
        ],
    ],

];
