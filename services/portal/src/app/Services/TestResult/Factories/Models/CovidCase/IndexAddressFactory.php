<?php

declare(strict_types=1);

namespace App\Services\TestResult\Factories\Models\CovidCase;

use App\Dto\TestResultReport\Address;
use App\Models\CovidCase\IndexAddress;
use App\Models\Versions\CovidCase\IndexAddress\IndexAddressV1;

final class IndexAddressFactory
{
    public static function create(Address $testResultAddress): IndexAddressV1
    {
        /** @var IndexAddressV1 $address */
        $address = IndexAddress::getSchema()->getVersion(1)->newInstance();

        $address->postalCode = $testResultAddress->postcode;
        $address->houseNumber = $testResultAddress->houseNumber;
        $address->houseNumberSuffix = $testResultAddress->houseNumberSuffix;
        $address->street = $testResultAddress->streetName;
        $address->town = $testResultAddress->city;

        return $address;
    }
}
