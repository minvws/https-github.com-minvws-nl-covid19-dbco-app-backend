<?php

declare(strict_types=1);

namespace App\Services\TestResult\Factories\Models;

use App\Dto\TestResultReport\Person;
use App\Models\Person\NameAndAddress;
use App\Models\Versions\Person\NameAndAddress\NameAndAddressV1;
use App\Services\TestResult\Factories\Enums\GenderFactory;
use App\Services\TestResult\Factories\Models\CovidCase\IndexAddressFactory;

final class NameAndAddressFactory
{
    public static function create(Person $person): NameAndAddressV1
    {
        /** @var NameAndAddressV1 $nameAndAddress */
        $nameAndAddress = NameAndAddress::getSchema()->getVersion(1)->newInstance();

        $nameAndAddress->initials = $person->initials;
        $nameAndAddress->firstname = $person->firstName;
        $nameAndAddress->lastname = $person->surname;
        $nameAndAddress->dateOfBirth = $person->dateOfBirth;
        $nameAndAddress->gender = GenderFactory::create($person->gender);
        $nameAndAddress->address = IndexAddressFactory::create($person->address);

        return $nameAndAddress;
    }
}
