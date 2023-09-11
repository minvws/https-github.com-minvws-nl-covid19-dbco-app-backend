<?php

declare(strict_types=1);

namespace App\Services\TestResult\Factories\Models;

use App\Dto\TestResultReport\Person as TestResultPerson;
use App\Models\Eloquent\Person;
use App\Repositories\Bsn\Dto\PseudoBsn;
use Carbon\CarbonImmutable;

final class PersonFactory
{
    public static function create(TestResultPerson $testResultPerson, ?PseudoBsn $pseudoBsn): Person
    {
        /** @var Person $person */
        $person = Person::getSchema()->getCurrentVersion()->newInstance();

        $person->date_of_birth = CarbonImmutable::instance($testResultPerson->dateOfBirth);
        $person->nameAndAddress = NameAndAddressFactory::create($testResultPerson);
        $person->contactDetails = ContactDetailsFactory::create($testResultPerson);

        if ($pseudoBsn instanceof PseudoBsn) {
            $person->pseudoBsnGuid = $pseudoBsn->getGuid();
        }

        return $person;
    }
}
