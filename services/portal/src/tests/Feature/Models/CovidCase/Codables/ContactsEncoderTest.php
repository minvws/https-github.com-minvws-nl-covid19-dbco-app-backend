<?php

declare(strict_types=1);

namespace Tests\Feature\Models\CovidCase\Codables;

use App\Models\CovidCase\Contacts;
use MinVWS\Codable\Encoder;
use Tests\Feature\FeatureTestCase;

class ContactsEncoderTest extends FeatureTestCase
{
    public function testEncode(): void
    {
        $contacts = $this->createFragment(Contacts::class);
        $encoded = (new Encoder())->encode($contacts);

        self::assertEquals($contacts->shareNameWithContacts, $encoded->shareNameWithContacts);
        self::assertEquals($contacts->estimatedMissingContacts, $encoded->estimatedMissingContacts);
        self::assertEquals($contacts->estimatedCategory1Contacts, $encoded->estimatedCategory1Contacts);
        self::assertEquals($contacts->estimatedCategory2Contacts, $encoded->estimatedCategory2Contacts);
        self::assertEquals($contacts->estimatedCategory3Contacts, $encoded->estimatedCategory3Contacts);
    }
}
