<?php

declare(strict_types=1);

namespace Tests\Unit\Helpers;

use App\Helpers\GgdSocCrypter;
use Mockery;
use Tests\TestCase;

use function random_bytes;
use function sodium_bin2base64;
use function strlen;

use const SODIUM_BASE64_VARIANT_URLSAFE_NO_PADDING;
use const SODIUM_CRYPTO_BOX_NONCEBYTES;
use const SODIUM_CRYPTO_BOX_PUBLICKEYBYTES;

class GgdSocCrypterTest extends TestCase
{
    public function testEcryptReturnsUsesRandomBytesAndCrypts(): void
    {
        $sender = GgdSocCrypter::generateKeySet();
        $receiver = GgdSocCrypter::generateKeySet();

        $nonce = $this->faker->word();
        $randomBytes = Mockery::spy(static fn ($byteCount) => $nonce);
        $crypt = Mockery::spy(static fn ($data, $nonce, $keyPair) => $data . $nonce);
        $crypter = new GgdSocCrypter($sender['secret'], $receiver['public'], $randomBytes, $crypt);
        $data = $this->faker->sentence();
        $encryptedDataWithNonce = $crypter->encrypt($data);

        $randomBytes->shouldHaveBeenCalled();
        $crypt->shouldHaveBeenCalled();
        self::assertEquals("{$nonce}{$data}{$nonce}", $encryptedDataWithNonce);
    }

    public function testEcryptReturnsIsUniqueEveryTimeDueToNonce(): void
    {
        $sender = GgdSocCrypter::generateKeySet();
        $receiver = GgdSocCrypter::generateKeySet();

        $crypter = new GgdSocCrypter($sender['secret'], $receiver['public']);
        $data = $this->faker->sentence();

        $firstEncryptedData = $crypter->encrypt($data);
        $secondEncryptedData = $crypter->encrypt($data);

        self::assertNotEquals($firstEncryptedData, $secondEncryptedData);
    }

    public function testGenerateKeySetReturnsPublicAndSecretKeys(): void
    {
        $keys = GgdSocCrypter::generateKeySet();
        foreach (['public', 'secret'] as $key) {
            self::assertArrayHasKey($key, $keys);
            $value = $keys[$key];
            self::assertIsString($value);
            self::assertEquals(SODIUM_CRYPTO_BOX_PUBLICKEYBYTES, strlen($value));
        }
    }

    public function testWithBase64KeysCreatesWorkingCrypter(): void
    {
        $senderKeys = GgdSocCrypter::generateKeySet();
        $receiverKeys = GgdSocCrypter::generateKeySet();
        $nonce = random_bytes(SODIUM_CRYPTO_BOX_NONCEBYTES);
        $randomBytes = static fn ($byteCount) => $nonce;
        $data = $this->faker->paragraph();
        $binaryKeyCrypter = new GgdSocCrypter($senderKeys['secret'], $receiverKeys['public'], $randomBytes);
        $base64KeyCrypter = GgdSocCrypter::withBase64Keys(
            sodium_bin2base64($senderKeys['secret'], SODIUM_BASE64_VARIANT_URLSAFE_NO_PADDING),
            sodium_bin2base64($receiverKeys['public'], SODIUM_BASE64_VARIANT_URLSAFE_NO_PADDING),
            $randomBytes,
        );
        $encryptedDataFromBinaryKeys = $binaryKeyCrypter->encrypt($data);
        $encryptedDataFromBase64Keys = $base64KeyCrypter->encrypt($data);
        self::assertEquals($encryptedDataFromBinaryKeys, $encryptedDataFromBase64Keys);
    }

    public function testWithBase64KeysDefaultsToRandomKeysWhenGivenBlankKeys(): void
    {
        $crypter = GgdSocCrypter::withBase64Keys('', '');
        $encryptedData = $crypter->encrypt($this->faker->paragraph());
        self::assertIsString($encryptedData);
    }
}
