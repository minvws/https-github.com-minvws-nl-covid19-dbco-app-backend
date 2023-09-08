<?php

declare(strict_types=1);

namespace App\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;

class EPIPatMWbronBuilder extends AbstractSingleValueBuilder
{
    protected function getValue(EloquentCase $case): ?string
    {
        $sources = Utils::getSources($case);

        if ($sources->count() === 1) {
            return '1'; // single
        }

        if ($sources->count() > 1) {
            return '2'; // multiple
        }

        $contactsAndSources = Utils::getContactsAndSources($case);
        if ($contactsAndSources->count() > 0) {
            return '3'; // no (we assume that entering contacts, but no possible sources means there are no known sources)
        }

        return '4'; // unknown
    }
}
