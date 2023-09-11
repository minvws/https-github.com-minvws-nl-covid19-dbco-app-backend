<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Encryption\Security\SecurityCacheFake;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\TestResponse;
use MinVWS\DBCO\Encryption\Security\SecurityCache;
use Tests\ModelCreator;
use Tests\TestCase;

use function random_bytes;
use function sprintf;

use const SODIUM_CRYPTO_SECRETBOX_KEYBYTES;

abstract class FeatureTestCase extends TestCase
{
    use DatabaseTransactions;
    use ModelCreator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->instance(SecurityCache::class, new SecurityCacheFake(random_bytes(SODIUM_CRYPTO_SECRETBOX_KEYBYTES)));
    }

    protected function assertStatus(TestResponse $response, int $status): TestResponse
    {
        if ($response->status() !== $status && $response->status() === 500) {
            echo sprintf('%s', $response->content());
        }

        return $response->assertStatus($status);
    }
}
