<?php

declare(strict_types=1);

return [
    'admin_view_enabled' => env('ADMIN_VIEW_ENABLED', true),

    'catalog_enabled' => (bool) env('CATALOG_ENABLED', true),

    'hpzone_operational' => env('HPZONE_OPERATIONAL', true),

    'intake_match_case_enabled' => env('INTAKE_MATCH_CASE_ENABLED', true),

    'message_template' => [
        'advice' => env('MESSAGE_TEMPLATE_ADVICE_ENABLED', true),
        'contact_infection' => env('MESSAGE_TEMPLATE_CONTACT_INFECTION_ENABLED', true),
        'deleted_message' => env('MESSAGE_TEMPLATE_DELETED_MESSAGE_ENABLED', true),
        'missed_phone' => env('MESSAGE_TEMPLATE_MISSED_PHONE_ENABLED', true),
    ],

    'metric_test_monthly' => env('METRIC_TEST_MONTHLY', true),

    'outsourcing_enabled' => (bool) env('OUTSOURCING_ENABLED', true),
    'outsourcing_to_regional_ggd_enabled' => (bool) env('OUTSOURCING_TO_REGIONAL_GDD_ENABLED', true),

    'planner_case_count_enabled' => (bool) env('PLANNER_CASE_COUNT_ENABLED', true),
    'planner_caselists_stats_0_enabled' => env('PLANNER_CASELISTS_STATS_0_ENABLED', false),

    'case_status_command_enabled' => env('CASE_STATUS_COMMAND_ENABLED', false),

    'covid_case_age_filter_enabled' => env('COVID_CASE_AGE_FILTER_ENABLED', true),
    'place_visited_tab_enabled' => env('PLACE_VISITED_TAB_ENABLED', true),
    'add_case_button_distributor_enabled' => env('ADD_CASE_BUTTON_DISTRIBUTOR_ENABLED', true),
    'add_case_button_user_enabled' => env('ADD_CASE_BUTTON_USER_ENABLED', true),
    'case_metrics_enabled' => env('CASE_METRICS_ENABLED', true),
    'osiris_retry_case_export_enabled' => env('OSIRIS_RETRY_CASE_EXPORT_ENABLED', true),
    'osiris_send_case_enabled' => env('OSIRIS_SEND_CASE_ENABLED', false),
    'purge_soft_deleted_models_enabled' => env('PURGE_SOFT_DELETED_MODELS_ENABLED', true),

    'migrate_data_off_hours' => env('MIGRATE_DATA_OFF_HOURS_ENABLED', false),
    'migrate_data_off_hours_scripts' => [
        'person_date_of_birth_encrypt' => env('MIGRATE_PERSON_DATE_OF_BIRTH_ENCRYPT_ENABLED', true),
    ],

    'diseases_and_dossiers_enabled' => env('DISEASES_AND_DOSSIERS_ENABLED', true),

    /* Audit Event - Schema Validation */
    'validate_audit_event_schema_enabled' => env('VALIDATE_AUDIT_EVENT_SCHEMA_ENABLED', true),
    'create_draft_audit_event_schema_enabled' => env('CREATE_DRAFT_AUDIT_EVENT_SCHEMA_ENABLED', false),
    'suppress_audit_event_schema_error_enabled' => env('SUPPRESS_AUDIT_EVENT_SCHEMA_ERROR_ENABLED', false),
    'measure_audit_event_schema_failure_enabled' => env('MEASURE_AUDIT_EVENT_SCHEMA_FAILURE_ENABLED', true),

    /* Audit Event - Spec Validation */
    'validate_audit_event_spec_enabled' => env('VALIDATE_AUDIT_EVENT_SPEC_ENABLED', true),
    'update_audit_event_spec_enabled' => env('UPDATE_AUDIT_EVENT_SPEC_ENABLED', false),
    'suppress_audit_event_spec_error_enabled' => env('SUPPRESS_AUDIT_EVENT_SPEC_ERROR_ENABLED', false),
    'measure_audit_event_spec_failure_enabled' => env('MEASURE_AUDIT_EVENT_SPEC_FAILURE_ENABLED', true),
];
