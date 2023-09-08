<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Exceptions\EncryptionException;
use App\Services\EncryptionService;
use Tests\TestCase;

use function base64_decode;
use function base64_encode;
use function json_encode;
use function sodium_crypto_box_keypair;
use function sodium_crypto_box_publickey;
use function sodium_crypto_box_seal_open;
use function sprintf;

final class EncryptionServiceTest extends TestCase
{
    /**
     * @dataProvider validDataProvider
     */
    public function testEncryptPayload(string $publicKeyPrefix): void
    {
        $keypair = sodium_crypto_box_keypair();
        $encryptionPublicKey = sodium_crypto_box_publickey($keypair);
        $publicKey = sprintf('%s%s', $publicKeyPrefix, base64_encode($encryptionPublicKey));

        $payload = self::getRequestPayloadForTestResult();

        $encryptionService = new EncryptionService($publicKey);
        $encodedString = $encryptionService->encryptPayload($payload);

        $this->assertSame(
            json_encode($payload),
            sodium_crypto_box_seal_open(
                base64_decode($encodedString),
                $keypair,
            ),
        );
    }

    public static function validDataProvider(): array
    {
        return [
            'without prefix' => [''],
            'with prefix' => ['base64:'],
        ];
    }

    /**
     * @dataProvider invalidConfigDataProvider
     */
    public function testEncryptTestResultReportWithInvalidConfig(string $publicKey): void
    {
        $this->expectException(EncryptionException::class);

        $encryptionService = new EncryptionService($publicKey);
        $encryptionService->encryptPayload(self::getRequestPayloadForTestResult());
    }

    public static function invalidConfigDataProvider(): array
    {
        return [
            'invalid base64' => ['foo'],
            'too short' => ['dmVyeXNlY3JldA=='],
        ];
    }
}
