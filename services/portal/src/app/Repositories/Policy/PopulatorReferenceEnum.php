<?php

declare(strict_types=1);

namespace App\Repositories\Policy;

enum PopulatorReferenceEnum: string
{
    case SourcePeriod = 'source_period';
    case ContagiousPeriod = 'contagious_period';

    case DateOfSymptomOnset = 'date_of_symptom_onset';
    case DateOfTestIndex = 'date_of_test_index';

    case QuarantinePeriod = 'quarantine_period';
    case DateOfQuarantineEnd = 'date_of_quarantine_end';
    case DateOfTestContact = 'date_of_test_contact';
}
