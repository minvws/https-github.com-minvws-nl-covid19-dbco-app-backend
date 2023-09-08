<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Securemail;

use App\Services\SecureMail\SecureMailClientManager;
use App\Services\SecureMail\SecureMailV1Client;
use App\Services\SecureMail\SecureMailV2Client;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\FeatureTestCase;
use Tests\Helpers\ConfigHelper;

class SecureMailClientManagerTest extends FeatureTestCase
{
    #[DataProvider('secureMailClientInstanceDataProvider')]
    public function testSecureMailClientInstance(?string $configValue, ?string $driver, string $expectedInstance): void
    {
        ConfigHelper::set('secure_mail.default', $configValue);
        ConfigHelper::set('secure_mail.v1.base_url', $this->faker->word);
        ConfigHelper::set('secure_mail.v1.jwt_secret', $this->faker->word);
        ConfigHelper::set('secure_mail.v2.base_url', $this->faker->word);
        ConfigHelper::set('secure_mail.v2.api_token', $this->faker->word);

        /** @var SecureMailClientManager $secureMailClientmanager */
        $secureMailClientmanager = $this->app->get(SecureMailClientManager::class);
        $secureMailClient = $secureMailClientmanager->driver($driver);

        $this->assertInstanceOf($expectedInstance, $secureMailClient);
    }

    public static function secureMailClientInstanceDataProvider(): array
    {
        return [
            'v1, null' => ['v1', null, SecureMailV1Client::class],
            'v1, v1' => ['v1', 'v1', SecureMailV1Client::class],
            'v1, v2' => ['v1', 'v2', SecureMailV2Client::class],
            'v2, null' => ['v2', null, SecureMailV2Client::class],
            'v2, v1' => ['v2', 'v1', SecureMailV1Client::class],
            'v2, v2' => ['v2', 'v2', SecureMailV2Client::class],
            'null, v1' => [null, 'v1', SecureMailV1Client::class],
            'null, v2' => [null, 'v2', SecureMailV2Client::class],
        ];
    }

    public function testSecureMailClientInstanceFailsWithExplicitNullConfig(): void
    {
        ConfigHelper::set('secure_mail.default', null);

        /** @var SecureMailClientManager $secureMailClientmanager */
        $secureMailClientmanager = $this->app->get(SecureMailClientManager::class);

        $this->expectException(InvalidArgumentException::class);
        $secureMailClientmanager->driver();
    }
}
