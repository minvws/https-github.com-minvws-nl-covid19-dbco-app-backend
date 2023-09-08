<?php

declare(strict_types=1);

namespace Tests\Unit\Console\Commands;

use App\Helpers\GgdSocCrypter;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

use function sodium_bin2base64;
use function sodium_bin2hex;
use function sprintf;

use const SODIUM_BASE64_VARIANT_ORIGINAL;
use const SODIUM_BASE64_VARIANT_ORIGINAL_NO_PADDING;
use const SODIUM_BASE64_VARIANT_URLSAFE;
use const SODIUM_BASE64_VARIANT_URLSAFE_NO_PADDING;

class ConvertGGDSOCKeysTest extends TestCase
{
    public function testConvertGGDSOCKeysRunsWithoutErrorFromHex(): void
    {
        $key = GgdSocCrypter::generateKeySet()['public'];
        $hex = sodium_bin2hex($key);

        $this->artisan(sprintf('convert:ggdsockeys -- %s', $hex))
            ->expectsOutput('Re-encoded key (URL-safe Base64 encoding, without = padding characters)')
            ->assertSuccessful();
    }

    #[DataProvider('validIdProvider')]
    public function testConvertGGDSOCKeysRunsWithoutErrorFromBase64(int $id): void
    {
        $key = GgdSocCrypter::generateKeySet()['public'];
        $base64 = sodium_bin2base64($key, $id);

        $this->artisan(sprintf('convert:ggdsockeys -- %s', $base64))
            ->expectsOutput('Re-encoded key (URL-safe Base64 encoding, without = padding characters)')
            ->assertSuccessful();
    }

    public static function validIdProvider(): array
    {
        return [
            'base64' => [SODIUM_BASE64_VARIANT_ORIGINAL],
            'base64 without padding' => [SODIUM_BASE64_VARIANT_ORIGINAL_NO_PADDING],
            'base64 url safe' => [SODIUM_BASE64_VARIANT_URLSAFE],
            'base64 url safe without padding' => [SODIUM_BASE64_VARIANT_URLSAFE_NO_PADDING],
        ];
    }

    public function testConvertGGDSOCKeysRunsShowsErrorForInvalidKey(): void
    {
        // Hardcoded key instead of faker, because it might generate a hex/base64 decodeable string
        $key = '__invalid__';
        $this->artisan(sprintf('convert:ggdsockeys -- %s', $key))
            ->expectsOutput('Key is not a hex or base64 encoded key.')
            ->assertFailed();
    }

    public function testConvertGGDSOCKeysRunsShowsErrorForInvalidKeyLength(): void
    {
        $key = GgdSocCrypter::generateKeySet()['public'];
        // Double the key length
        $invalidKey = sprintf('%s%s', $key, $key);
        $encodedKey = sodium_bin2hex($invalidKey);
        $this->artisan(sprintf('convert:ggdsockeys -- %s', $encodedKey))
            ->expectsOutput('Key does not have the expected length.')
            ->assertFailed();
    }
}
