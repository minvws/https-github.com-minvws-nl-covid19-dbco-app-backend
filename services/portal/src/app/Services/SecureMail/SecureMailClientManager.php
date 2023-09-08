<?php

declare(strict_types=1);

namespace App\Services\SecureMail;

use App\Helpers\Config;
use Illuminate\Support\Manager;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class SecureMailClientManager extends Manager
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function createV1Driver(): SecureMailV1Client
    {
        /** @var SecureMailV1Client $secureMailV1Client */
        $secureMailV1Client = $this->container->get(SecureMailV1Client::class);

        return $secureMailV1Client;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function createV2Driver(): SecureMailV2Client
    {
        /** @var SecureMailV2Client $secureMailV2Client */
        $secureMailV2Client = $this->container->get(SecureMailV2Client::class);

        return $secureMailV2Client;
    }

    public function getDefaultDriver(): string
    {
        return Config::string('secure_mail.default', 'v1');
    }
}
