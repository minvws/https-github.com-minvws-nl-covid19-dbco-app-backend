<?php

declare(strict_types=1);

namespace Tests\Feature\Providers;

use App\Encryption\Security\SecurityCacheFake;
use App\Providers\SecurityServiceProvider;
use MinVWS\DBCO\Encryption\Security\SecurityCache;
use Tests\Feature\FeatureTestCase;

use function config;

class SecurityServiceProviderTest extends FeatureTestCase
{
    public function testSecurityCacheFakeInstance(): void
    {
        $expectedValue = $this->faker->word();
        config()->set('security.useFakeHSM', true);

        $securityServiceProvider = new SecurityServiceProvider($this->app);
        $securityServiceProvider->register();

        $this->mock(SecurityCacheFake::class)
            ->expects('getValue')
            ->andReturn($expectedValue);

        $securityCache = $this->app->get(SecurityCache::class);
        $actualValue = $securityCache->getValue($this->faker->word());

        $this->assertEquals($expectedValue, $actualValue);
    }
}
