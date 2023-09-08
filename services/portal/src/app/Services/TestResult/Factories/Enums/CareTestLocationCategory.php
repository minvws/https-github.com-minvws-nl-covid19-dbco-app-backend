<?php

declare(strict_types=1);

namespace App\Services\TestResult\Factories\Enums;

enum CareTestLocationCategory: string
{
    // meldportaal values
    case GEHANDICAPTENZORG = 'gehandicaptenzorg';
    case HUISARTS = 'huisarts';
    case HUISARTSENPOST = 'huisartsenpost';
    case ZIEKENHUIS = 'ziekenhuis';
    case VVT = 'vvt';

    // ESB mapping of the meldportaal values
    case DISABLED_CARE = 'disabled_care';
    case GENERAL_PRACTICE_CENTER = 'general_practice_center';
    case HOSPITAL = 'hospital';
    case GENERAL_PRATITIONER = 'general_pratitioner'; // 'pratitioner' : we receive this typo according to the documentation (june 2023)
    case GENERAL_PRACTITIONER = 'general_practitioner'; // case without typo for robustness
}
