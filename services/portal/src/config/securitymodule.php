<?php

declare(strict_types=1);

return [
    'type' => env('SECURITY_MODULE_TYPE', 'hsm'),
    'sim_key_path' => env('SECURITY_MODULE_SIM_KEY_PATH', '/tmp'),
    'store_key_time_zone' => 'Europe/Amsterdam',
    'store_key_max_days' => 14, // max days to store earlier keys for unsealing
];
