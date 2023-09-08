<?php

declare(strict_types=1);

use App\Services\Intake\IntakeService;
use App\Services\MessageQueue\AMQPConnection;

return [
    'types' => [
        'amqp' => AMQPConnection::class,
    ],

    'connections' => [
        'default' => [
            'type' => 'amqp',
            'host' => env('AMQP_HOST', 'rabbitmq'),
            'port' => env('AMQP_PORT', 5672),
            'username' => env('AMQP_USERNAME', 'guest'),
            'password' => env('AMQP_PASSWORD', 'guest'),
            'consumerTag' => env('AMQP_CONSUMER_TAG', 'dbco-portal'),
            'vhost' => env('AMQP_VHOST', '/'),
        ],
    ],

    'queues' => [
        'intake' => [
            'declare_exchange_and_queue' => env('AMQP_QUEUE_INTAKE_DECLARE_EXCHANGE_AND_QUEUE', false),
            'queue' => env('AMQP_QUEUE_INTAKE_QUEUE', 'intake'),
            'exchange' => env('AMQP_QUEUE_INTAKE_EXCHANGE', 'dbco'),
            'routing_key' => env('AMQP_QUEUE_INTAKE_ROUTING_KEY', 'dbco.intake'),
            'dead_letter_exchange' => env('AMQP_QUEUE_INTAKE_DEAD_EXCHANGE', 'dbco'),
            'dead_letter_routing_key' => env('AMQP_QUEUE_INTAKE_DEAD_LETTER_QUEUE', 'dbco.intake.failed'),
            'delivery_limit' => env('AMQP_QUEUE_INTAKE_DELIVERY_LIMIT', 3),
        ],
    ],

    'processors' => [
        [
            'class' => IntakeService::class,
            'queues' => ['intake'],
        ],
    ],
];
