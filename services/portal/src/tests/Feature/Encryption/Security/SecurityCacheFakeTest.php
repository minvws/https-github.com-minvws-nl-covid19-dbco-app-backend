<?php

declare(strict_types=1);

namespace Tests\Feature\Encryption\Security;

use App\Encryption\Security\SecurityCacheFake;
use Illuminate\Support\Facades\App;
use RuntimeException;
use Tests\Feature\FeatureTestCase;

use function random_bytes;

use const SODIUM_CRYPTO_SECRETBOX_KEYBYTES;

class SecurityCacheFakeTest extends FeatureTestCase
{
    private string $key;
    private SecurityCacheFake $securityCacheFake;

    protected function setUp(): void
    {
        parent::setUp();

        $this->key = random_bytes(SODIUM_CRYPTO_SECRETBOX_KEYBYTES);
        $this->securityCacheFake = new SecurityCacheFake($this->key);
    }

    public function testFailOnProduction(): void
    {
        App::expects('environment')
            ->with(['production'])
            ->andReturn(true);

        $this->expectException(RuntimeException::class);
        new SecurityCacheFake($this->faker->word());
    }

    public function testHasValue(): void
    {
        $this->assertTrue($this->securityCacheFake->hasValue($this->faker->word()));
    }

    public function testGetValue(): void
    {
        $this->assertEquals($this->key, $this->securityCacheFake->getValue($this->faker->word()));
    }

    public function testDeleteValue(): void
    {
        $this->assertTrue($this->securityCacheFake->deleteValue($this->faker->word()));
    }

    public function testHasSecretKey(): void
    {
        $this->assertTrue($this->securityCacheFake->hasSecretKey($this->faker->word()));
    }

    public function testGetSecretKey(): void
    {
        $this->assertEquals($this->key, $this->securityCacheFake->getSecretKey($this->faker->word()));
    }

    public function testDeleteSecretKey(): void
    {
        $this->assertTrue($this->securityCacheFake->deleteSecretKey($this->faker->word()));
    }
}
