<?php

declare(strict_types=1);

return [
    'roles' => [
        'user' => env('USER_ROLE_USER', 'bco_user'),
        'user_nationwide' => env('USER_ROLE_USER_NATIONWIDE', 'bco_user_nationwide'),
        'admin' => env('USER_ROLE_ADMIN', 'bco_admin'),
        'planner' => env('USER_ROLE_PLANNER', 'bco_planner'),
        'planner_nationwide' => env('USER_ROLE_PLANNER_NATIONWIDE', 'bco_planner_nationwide'),
        'user_planner' => env('USER_ROLE_USER_PLANNER', 'bco_user_planner'),
        'compliance' => env('USER_ROLE_COMPLIANCE', 'bco_compliance'),
        'contextmanager' => env('USER_ROLE_CONTEXTMANAGER', 'bco_contextmanager'),
        'casequality' => env('USER_ROLE_CASEQUALITY', 'bco_casequality'),
        'casequality_nationwide' => env('USER_ROLE_CASEQUALITY_NATIONWIDE', 'bco_casequality_nationwide'),
        'medical_supervisor' => env('USER_ROLE_MEDICAL_SUPERVISOR', 'bco_medical_supervisor'),
        'medical_supervisor_nationwide' => env('USER_ROLE_MEDICAL_SUPERVISOR_NATIONWIDE', 'bco_medical_supervisor_nationwide'),
        'conversation_coach' => env('USER_ROLE_CONVERSATION_COACH', 'bco_conversation_coach'),
        'conversation_coach_nationwide' => env('USER_ROLE_CONVERSATION_COACH_NATIONWIDE', 'bco_conversation_coach_nationwide'),
        'callcenter' => env('USER_ROLE_CALLCENTER', 'bco_callcenter'),
        'callcenter_expert' => env('USER_ROLE_CALLCENTER_EXPERT', 'bco_callcenter_expert'),
        'datacatalog' => env('USER_ROLE_DATACATALOG', 'bco_datacatalog'),
        'clusterspecialist' => env('USER_ROLE_CLUSTERSPECIALIST', 'bco_clusterspecialist'),
    ],
    'nationwide_roles' => [
        'user_nationwide',
        'planner_nationwide',
        'casequality_nationwide',
        'medical_supervisor_nationwide',
        'conversation_coach_nationwide',
    ],
];
