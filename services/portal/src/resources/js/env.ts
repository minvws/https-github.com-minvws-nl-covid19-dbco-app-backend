export interface Environment {
    version: string;
    isAdminViewEnabled: boolean;
    isPlannerGetCaselistsStats0Enabled: boolean;
    isIntakeMatchCaseEnabled: boolean;
    isHpzoneOperational: boolean;
    isCaseMetricsEnabled: boolean;
    isOutsourcingEnabled: boolean;
    isOutsourcingToRegionalGGDEnabled: boolean;
    isCovidCaseAgeFilterEnabled: boolean;
    isPlaceVisitedTabEnabled: boolean;
    isAddCaseButtonDistributorEnabled: boolean;
    isAddCaseButtonUserEnabled: boolean;
}

const env: Environment = {
    version: import.meta.env.VITE_APP_VERSION || 'latest',
    isAdminViewEnabled: window.config.misc.admin_view_enabled,
    isPlannerGetCaselistsStats0Enabled: window.config?.misc?.planner_caselists_stats_0_enabled,
    isIntakeMatchCaseEnabled: window.config?.intake?.match_case_enabled,
    isCaseMetricsEnabled: window.config.misc.case_metrics_enabled,
    isHpzoneOperational: window.config?.misc?.hpzone_operational,
    isOutsourcingEnabled: window.config?.outsourcing?.outsourcing_to_regional_ggd_enabled,
    isOutsourcingToRegionalGGDEnabled: window.config?.outsourcing?.outsourcing_to_regional_ggd_enabled,
    isCovidCaseAgeFilterEnabled: window.config?.misc?.covid_case_age_filter_enabled,
    isPlaceVisitedTabEnabled: window.config?.misc?.place_visited_tab_enabled,
    isAddCaseButtonDistributorEnabled: window.config?.misc?.add_case_button_distributor_enabled,
    isAddCaseButtonUserEnabled: window.config?.misc?.add_case_button_user_enabled,
};

export default env;
