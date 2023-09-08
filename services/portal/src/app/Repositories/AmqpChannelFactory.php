<?php

declare(strict_types=1);

namespace App\Repositories;

use MelchiorKokernoot\LaravelAutowireConfig\Config\Config;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;

/**
 * @codeCoverageIgnore
 * Ignored because it's a factory for a library class and is only used to make testing easier.
 */
class AmqpChannelFactory
{
    public function __construct(
        #[Config('audit_logging.rabbit_mq_host')]
        private readonly string $host,
        #[Config('audit_logging.rabbit_mq_port')]
        private readonly int $port,
        #[Config('audit_logging.rabbit_mq_user')]
        private readonly string $user,
        #[Config('audit_logging.rabbit_mq_password')]
        private readonly string $password,
        #[Config('audit_logging.rabbit_mq_vhost')]
        private readonly string $vhost,
    )
    {
    }

    public function create(): AMQPChannel
    {
        $connection = new AMQPStreamConnection($this->host, $this->port, $this->user, $this->password, $this->vhost);
        return $connection->channel();
    }
}
