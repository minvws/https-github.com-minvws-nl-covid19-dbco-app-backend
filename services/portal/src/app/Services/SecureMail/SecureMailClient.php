<?php

declare(strict_types=1);

namespace App\Services\SecureMail;

use DateTimeInterface;

interface SecureMailClient
{
    /**
     * @throws SecureMailException
     */
    public function postMessage(SecureMailMessage $message): string;

    /**
     * @throws SecureMailException
     */
    public function getSecureMailStatusUpdates(DateTimeInterface $since): SecureMailStatusUpdateCollection;

    /**
     * @throws SecureMailException
     */
    public function delete(string $uuid): void;
}
