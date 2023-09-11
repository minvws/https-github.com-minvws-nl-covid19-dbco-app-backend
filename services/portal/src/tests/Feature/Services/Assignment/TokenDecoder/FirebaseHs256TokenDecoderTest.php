<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Assignment\TokenDecoder;

use App\Services\Assignment\TokenDecoder\FirebaseHs256TokenDecoder;
use App\Services\Assignment\TokenDecoder\TokenDecoder;
use Firebase\JWT\JWT;
use Illuminate\Contracts\Config\Repository as Config;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

#[Group('assignment')]
final class FirebaseHs256TokenDecoderTest extends FeatureTestCase
{
    private Config $config;

    protected function setUp(): void
    {
        parent::setUp();

        $this->config = $this->app->make(Config::class);

        $this->config->set('assignment.jwt.secret', 'MY_JWT_SECRET');
    }

    public function testItCanBeInitialized(): void
    {
        $decoder = $this->app->make(FirebaseHs256TokenDecoder::class);

        $this->assertInstanceOf(FirebaseHs256TokenDecoder::class, $decoder);
        $this->assertInstanceOf(TokenDecoder::class, $decoder);
    }

    public function testDecode(): void
    {
        /** @var Config $config */
        $config = $this->app->make(Config::class);
        $config->set('assignment.jwt.secret', $secret = 'MY_JWT_SECRET');

        /** @var FirebaseHs256TokenDecoder $decoder */
        $decoder = $this->app->make(FirebaseHs256TokenDecoder::class);

        $expected = [
            $this->faker->unique()->word() => $this->faker->words(),
            $this->faker->unique()->word() => $this->faker->words(),
        ];

        $token = $this->createToken($expected, $secret);

        $this->assertEquals((object) $expected, $decoder->decode($token));
    }

    private function createToken(array $payload): string
    {
        return JWT::encode($payload, $this->config->get('assignment.jwt.secret'), 'HS256');
    }
}
