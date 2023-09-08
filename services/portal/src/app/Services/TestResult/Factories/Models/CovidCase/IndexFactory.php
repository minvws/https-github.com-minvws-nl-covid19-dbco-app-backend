<?php

declare(strict_types=1);

namespace App\Services\TestResult\Factories\Models\CovidCase;

use App\Dto\TestResultReport\Person;
use App\Models\CovidCase\Index;
use App\Models\Versions\CovidCase\Index\IndexV2;
use App\Repositories\Bsn\Dto\PseudoBsn;
use App\Services\TestResult\Factories\Enums\GenderFactory;

final class IndexFactory
{
    public static function create(Person $person, ?PseudoBsn $pseudoBsn): IndexV2
    {
        /** @var IndexV2 $index */
        $index = Index::getSchema()->getVersion(2)->newInstance();

        $index->initials = $person->initials;
        $index->firstname = $person->firstName;
        $index->lastname = $person->surname;
        $index->bsnCensored = $pseudoBsn?->getCensoredBsn();
        $index->bsnLetters = $pseudoBsn?->getLetters();
        $index->dateOfBirth = $person->dateOfBirth;
        $index->gender = GenderFactory::create($person->gender);
        $index->address = IndexAddressFactory::create($person->address);

        return $index;
    }
}
