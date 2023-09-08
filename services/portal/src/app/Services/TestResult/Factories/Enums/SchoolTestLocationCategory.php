<?php

declare(strict_types=1);

namespace App\Services\TestResult\Factories\Enums;

enum SchoolTestLocationCategory: string
{
    // meldportaal values
    case ONDERWIJS = 'onderwijs';

    // ESB mapping of the meldportaal values
    case EDUCATION = 'education';
}
