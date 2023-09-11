declare interface Window {
    app: Vue & import('vue').ComponentCustomProperties;
    config: {
        version: string;
        intake: {
            match_case_enabled: boolean;
        };
        misc: {
            admin_view_enabled: boolean;
            planner_caselists_stats_0_enabled: boolean;
            case_metrics_enabled: boolean;
            hpzone_operational: true;
            covid_case_age_filter_enabled: boolean;
            place_visited_tab_enabled: boolean;
            add_case_button_distributor_enabled: boolean;
            add_case_button_user_enabled: boolean;
        };
        outsourcing: {
            outsourcing_to_regional_ggd_enabled: boolean;
        };
    };
}
