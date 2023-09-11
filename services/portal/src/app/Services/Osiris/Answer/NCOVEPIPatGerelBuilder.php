<?php

declare(strict_types=1);

namespace App\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;

class NCOVEPIPatGerelBuilder extends AbstractSingleValueBuilder
{
    protected function getValue(EloquentCase $case): ?string
    {
        $sources = Utils::getSources($case);
        if ($sources->count() > 0) {
            return 'J'; // yes
        }

        $contacts = Utils::getContacts($case);
        if ($contacts->count() > 0) {
            return 'N'; // no; contacts entered, but no sources, so assume no
        }

        return 'Onb'; // unknown
    }
}
