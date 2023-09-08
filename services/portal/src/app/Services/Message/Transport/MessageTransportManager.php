<?php

declare(strict_types=1);

namespace App\Services\Message\Transport;

use Illuminate\Support\Manager;
use Psr\Container\ContainerExceptionInterface;

class MessageTransportManager extends Manager
{
    /**
     * @throws ContainerExceptionInterface
     */
    public function createSecureMailDriver(): SecureMailMessageTransport
    {
        return $this->container->get(SecureMailMessageTransport::class);
    }

    /**
     * @throws ContainerExceptionInterface
     */
    public function createSmtpDriver(): SmtpMailerMessageTransport
    {
        return $this->container->get(SmtpMailerMessageTransport::class);
    }

    /**
     * @throws ContainerExceptionInterface
     */
    public function createZivverDriver(): ZivverMessageTransport
    {
        return $this->container->get(ZivverMessageTransport::class);
    }

    public function getDefaultDriver(): string
    {
        return $this->config->get('messagetransport.secure', 'secure_mail');
    }
}
