<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\EncryptionException;
use Throwable;

use function base64_decode;
use function base64_encode;
use function json_encode;
use function sodium_crypto_box_seal;
use function str_starts_with;
use function substr;

use const JSON_THROW_ON_ERROR;

final class EncryptionService
{
    private string $publicKey;

    public function __construct(string $publicKey)
    {
        $this->publicKey = $publicKey;
    }

    public function encryptPayload(array $payload): string
    {
        try {
            $publicKey = $this->publicKey;
            if (str_starts_with($this->publicKey, 'base64:')) {
                $publicKey = substr($this->publicKey, 7);
            }

            $sealed = sodium_crypto_box_seal(
                json_encode($payload, JSON_THROW_ON_ERROR),
                base64_decode($publicKey),
            );

            return base64_encode($sealed);
        } catch (Throwable $throwable) {
            throw EncryptionException::fromThrowable($throwable);
        }
    }
}
