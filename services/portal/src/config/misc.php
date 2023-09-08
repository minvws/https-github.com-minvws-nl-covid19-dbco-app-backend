<?php

declare(strict_types=1);

$getIntEnv = static function (string $envName, int $default): int {
    $value = env($envName);
    if (is_numeric($value) && is_int($value + 0)) {
        return (int) $value;
    }

    return $default;
};

return [
    'case' => [
        // the interval (seconds) after case creation in which pairing is allowed
        'pairingAllowedInterval' => 60 * 60 * 24 * 5, // 5 days
        'assignment' => [
            'lastLoginThresholdNeededForCaseAssignmentInDays' => 14,
        ],
        'ownerNewCaseEditIntervalInSeconds' => 60 * 60 * 24, // 1 day,
        'archiveStaleCompletedCasesInDays' => 28,
        'index_age' => [
            'queue' => [
                'connection' => env('QUEUE_CONNECTION', 'sync'),
                'queue_name' => env('CALCULATE_AGE_JOB_QUEUE_NAME', 'default'),
            ],
        ],
    ],
    'place' => [
        // Default every 15 minutes
        'counters' => [
            'queue' => [
                'connection' => env('QUEUE_CONNECTION', 'sync'),
                'queue_name' => env('SYNC_PLACE_COUNTERS_JOB_QUEUE_NAME', 'default'),
            ],
        ],
    ],
    'caseLock' => [
        // Set with minutes
        'lifetime' => max(1, $getIntEnv('CASE_LOCK_LIFETIME', 3)),
    ],
    'planner' => [
        'case_count_limit' => max(0, $getIntEnv('PLANNER_CASE_COUNT_LIMIT', 0)),
        'case_recent_days' => max(1, $getIntEnv('PLANNER_CASE_RECENT_DAYS', 14)),
    ],
    'supervision' => [
        'questions_recent_days' => max(1, $getIntEnv('SUPERVISION_QUESTIONS_RECENT_DAYS', 4)),
    ],
    'bcoNumbers' => [
        'maxRetries' => 20,
        'allowedNumberChars' => '0123456789',
        'allowedAlphaChars' => 'BCFGJLQRSTUVXYZ',
    ],
    'context' => [
        'unique_index_count_recent_days' => max(1, $getIntEnv('CONTEXT_UNIQUE_INDEX_COUNT_RECENT_DAYS', 28)),
    ],
    'validations' => [
        //Date before case creation which is used to validate dates to far back in the past
        'maxBeforeCaseCreationDateInDays' => 100,
        //Due date should not be to far in the past
        'maxDueDateBeforeCaseCreationInDays' => 30,
        //Due date should not be to far in the future
        'maxDueDateAfterCaseCreationInMonths' => 10,
        //Recent birthdate should not be to far in the past
        'maxRecentBirthBeforeCaseCreationInWeeks' => 6,
        //Departure date for abroad travel should not be to far in the past
        'maxAbroadDepartureBeforeCaseCreationInYears' => 1,
        'startOfCovidSurveillanceDate' => '2020-03-01 00:00:00',
        'firstReportedCovidCaseDate' => '2020-02-27 00:00:00',
        'firstAllowableDateOfSymptomOnset' => '2020-01-01 00:00:00',
        'firstAllowableDateOfBirth' => '1906-01-01',
    ],
    'commands' => [
        'importZipcodes' => [
            'defaultFile' => 'data/PC6-GGDregio.csv',
        ],
        'purge_stale_chores' => [
            'default_chunk_size' => 100,
            'default_usleep' => 100,
        ],
    ],
    'secure_mail' => [
        'default_expiry_in_days' => env('SECURE_MAIL_DEFAULT_EXPIRY_IN_DAYS', 30),
    ],
    'encryption' => [
        'task_availability_in_days' => $getIntEnv('ENCRYPTION_TASK_AVAILABILITY_IN_DAYS', 28),
    ],
    'intake' => [
        'force_region_code' => env('INTAKE_FORCE_REGION_CODE', false),
        'identity_data_public_key' => env('INTAKE_IDENTITY_DATA_PUBLIC_KEY'),
    ],
    'test_result' => [
        'covid_case_assignment_period_in_weeks' => env('TEST_RESULT_TO_COVID_CASE_ASSIGNMENT_PERIOD_IN_WEEKS', 8),
        'simulation_mode_enabled' => env('TEST_RESULT_SIMULATION_MODE_ENABLED', false),
    ],
];
