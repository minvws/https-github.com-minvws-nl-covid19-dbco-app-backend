<?php

declare(strict_types=1);

namespace App\Services\TestResult\Factories\Models\CovidCase;

use App\Dto\TestResultReport\Person;
use App\Models\CovidCase\Contact;
use App\Models\Versions\CovidCase\Contact\ContactV1;

final class ContactFactory
{
    public static function create(Person $person): ContactV1
    {
        /** @var ContactV1 $contact */
        $contact = Contact::getSchema()->getVersion(1)->newInstance();

        $contact->email = $person->email;
        $contact->phone = $person->telephoneNumber;

        return $contact;
    }
}
