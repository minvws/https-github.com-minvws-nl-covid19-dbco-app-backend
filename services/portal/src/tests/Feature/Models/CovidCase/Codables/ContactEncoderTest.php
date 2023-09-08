<?php

declare(strict_types=1);

namespace Tests\Feature\Models\CovidCase\Codables;

use App\Models\CovidCase\Contact;
use MinVWS\Codable\Encoder;
use Tests\Feature\FeatureTestCase;

class ContactEncoderTest extends FeatureTestCase
{
    public function testEncode(): void
    {
        $contact = $this->createFragment(Contact::class);
        $encoded = (new Encoder())->encode($contact);

        self::assertEquals($contact->phone, $encoded->phone);
        self::assertEquals($contact->email, $encoded->email);
    }
}
