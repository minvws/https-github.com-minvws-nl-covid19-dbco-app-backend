<?php

declare(strict_types=1);

namespace App\Services\TestResult\Factories\Models;

use App\Dto\TestResultReport\Person;
use App\Models\Person\ContactDetails;
use App\Models\Versions\Person\ContactDetails\ContactDetailsV1;

final class ContactDetailsFactory
{
    public static function create(Person $person): ContactDetailsV1
    {
        /** @var ContactDetailsV1 $contactDetails */
        $contactDetails = ContactDetails::getSchema()->getVersion(1)->newInstance();

        $contactDetails->phone = $person->telephoneNumber;
        $contactDetails->email = $person->email;

        return $contactDetails;
    }
}
