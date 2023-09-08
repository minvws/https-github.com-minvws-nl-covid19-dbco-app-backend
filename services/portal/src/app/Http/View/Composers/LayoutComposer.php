<?php

declare(strict_types=1);

namespace App\Http\View\Composers;

use App\Helpers\FeatureFlagHelper;
use Illuminate\View\View;

use function collect;
use function config;

class LayoutComposer
{
    public function compose(View $view): void
    {
        $frontendConfiguration = collect([
            'version' => config('app.env_version'),
            'intake' => [
                'match_case_enabled' => FeatureFlagHelper::isEnabled('intake_match_case_enabled'),
            ],
            'misc' => [
                'planner_caselists_stats_0_enabled' => FeatureFlagHelper::isEnabled(
                    'planner_caselists_stats_0_enabled',
                ),
                'admin_view_enabled' => FeatureFlagHelper::isEnabled('admin_view_enabled'),
                'covid_case_age_filter_enabled' => FeatureFlagHelper::isEnabled('covid_case_age_filter_enabled'),
                'place_visited_tab_enabled' => FeatureFlagHelper::isEnabled('place_visited_tab_enabled'),
                'add_case_button_distributor_enabled' => FeatureFlagHelper::isEnabled('add_case_button_distributor_enabled'),
                'add_case_button_user_enabled' => FeatureFlagHelper::isEnabled('add_case_button_user_enabled'),
                'case_metrics_enabled' => FeatureFlagHelper::isEnabled('case_metrics_enabled'),
                'hpzone_operational' => FeatureFlagHelper::isEnabled('hpzone_operational'),
            ],
            'outsourcing' => [
                'enabled' => FeatureFlagHelper::isEnabled('outsourcing_enabled'),
                'outsourcing_to_regional_ggd_enabled' => FeatureFlagHelper::isEnabled('outsourcing_to_regional_ggd_enabled'),
            ],
        ]);

        $view->with('frontendConfiguration', $frontendConfiguration);
    }
}
