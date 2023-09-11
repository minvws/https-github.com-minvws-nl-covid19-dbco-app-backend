<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;
use Tests\Helpers\ConfigHelper;

use function is_array;
use function json_encode;

#[Group('layout')]
class LayoutTest extends FeatureTestCase
{
    #[DataProvider('configurationDataProvider')]
    public function testFrontendConfiguration(bool $enabled): void
    {
        $this->enableAllFeatureFlags(ConfigHelper::get('featureflag'), $enabled);
        ConfigHelper::set('app.env_version', '1');

        $user = $this->createUser([
            'consented_at' => CarbonImmutable::now(),
        ]);

        $response = $this->be($user)->get('/cases');
        $response->assertStatus(200);

        $frontendConfiguration = json_encode([
            'version' => '1',
            'intake' => [
                'match_case_enabled' => $enabled,
            ],
            'misc' => [
                'planner_caselists_stats_0_enabled' => $enabled,
                'admin_view_enabled' => $enabled,
                'covid_case_age_filter_enabled' => $enabled,
                'place_visited_tab_enabled' => $enabled,
                'add_case_button_distributor_enabled' => $enabled,
                'add_case_button_user_enabled' => $enabled,
                'case_metrics_enabled' => $enabled,
                'hpzone_operational' => $enabled,
            ],
            'outsourcing' => [
                'enabled' => $enabled,
                'outsourcing_to_regional_ggd_enabled' => $enabled,
            ],
        ]);

        $response->assertSeeInOrder([
            '<script nonce="',
            // nonce value
            '">',
            'window.config = ',
            $frontendConfiguration,
            ';',
            '</script>',
        ], false);
    }

    public static function configurationDataProvider(): array
    {
        return [
            'enabled' => [true],
            'disabled' => [false],
        ];
    }

    private function enableAllFeatureFlags(array $featureFlags, bool $enabled): void
    {
        // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
        foreach ($featureFlags as $featureFlagKey => $featureFlagValue) {
            if (is_array($featureFlagKey)) {
                $this->enableAllFeatureFlags($featureFlagKey, $enabled);
            }

            ConfigHelper::setFeatureFlag($featureFlagKey, $enabled);
        }
    }
}
