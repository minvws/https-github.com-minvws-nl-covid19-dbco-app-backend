<?php

declare(strict_types=1);

namespace App\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentTask;
use MinVWS\DBCO\Enum\Models\ContactCategory;

class NCOVNContactCat1Builder extends AbstractSingleValueBuilder
{
    protected function getValue(EloquentCase $case): ?string
    {
        return (string) Utils::getContacts($case)
            ->filter(static fn (EloquentTask $contact) => $contact->category === ContactCategory::cat1())
            ->count();
    }
}
