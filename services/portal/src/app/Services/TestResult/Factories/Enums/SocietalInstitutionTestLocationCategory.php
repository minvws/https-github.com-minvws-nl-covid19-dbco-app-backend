<?php

declare(strict_types=1);

namespace App\Services\TestResult\Factories\Enums;

enum SocietalInstitutionTestLocationCategory: string
{
    // meldportaal values
    case ASIELZOEKERSCENTRUM = 'asielzoekerscentrum';
    case PENITENTIAIRE_INRICHTING = 'penitentiaire inrichting';

    // ESB mapping of the meldportaal values
    case ASYLUM_CENTER = 'asylum_center';
    case PRISON = 'prison';
}
