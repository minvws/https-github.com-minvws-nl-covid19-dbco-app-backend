<?php

declare(strict_types=1);

namespace Tests\Feature\Helpers;

use App\Helpers\FeatureFlagHelper;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\FeatureTestCase;

use function config;
use function sprintf;

class FeatureFlagHelperTest extends FeatureTestCase
{
    #[DataProvider('featureFlagDataProvider')]
    public function testIsEnabled(string $configKey, mixed $configValue, bool $expectedResult): void
    {
        config()->set(sprintf('featureflag.%s', $configKey), $configValue);

        $result = FeatureFlagHelper::isEnabled($configKey);

        $this->assertEquals($expectedResult, $result);
    }

    #[DataProvider('featureFlagDataProvider')]
    public function testIsDisabled(string $configKey, mixed $configValue, bool $expectedResult): void
    {
        config()->set(sprintf('featureflag.%s', $configKey), $configValue);

        $result = FeatureFlagHelper::isDisabled($configKey);

        $this->assertEquals(!$expectedResult, $result);
    }

    public static function featureFlagDataProvider(): array
    {
        return [
            // only expected disabled if explicitly set to false
            'feature explicitly disabled' => ['metric_test_monthly', false, false],
            // all other values will return true
            'feature value = true' => ['metric_test_monthly', true, true],
            'feature value = null' => ['metric_test_monthly', null, true],
            'feature value = 0,' => ['metric_test_monthly', 0, true],
            'feature value = 1,' => ['metric_test_monthly', 1, true],
            'feature value = foo' => ['metric_test_monthly', 'foo', true],
            'feature value = []' => ['metric_test_monthly', [], true],
        ];
    }

    #[DataProvider('multipleFeatureFlagsDataProvider')]
    public function testMultipleFeatureFlags(
        bool|string|null $featureFlag1,
        ?bool $featureFlag2,
        ?bool $featureFlag3,
        bool $expectedResult,
    ): void {
        config()->set('featureflag.feature1', $featureFlag1);
        config()->set('featureflag.feature2', $featureFlag2);
        config()->set('featureflag.feature3', $featureFlag3);

        $result = FeatureFlagHelper::isEnabled('feature1', 'feature2', 'feature3');

        $this->assertEquals($expectedResult, $result);
    }

    public static function multipleFeatureFlagsDataProvider(): array
    {
        return [
            'all enabled' => [true, true, true, true],
            'only one enabled' => [true, false, false, false],
            'only two enabled' => [true, true, false, false],
            'all disabled' => [false, false, false, false],
            'one null' => [null, true, true, true],
            'all null' => [null, null, null, true],
            'one string' => ['foo', true, true, true],
            'one string, one disabled' => ['foo', true, false, false],
        ];
    }

    public function testIsEnabledWhenConfigNotFound(): void
    {
        $result = FeatureFlagHelper::isEnabled($this->faker->word());

        $this->assertTrue($result);
    }
}
