<?php

declare(strict_types=1);

namespace Tests\Feature\Models\CovidCase\Codables;

use App\Models\CovidCase\AlternateContact;
use MinVWS\Codable\Encoder;
use MinVWS\DBCO\Enum\Models\Gender;
use MinVWS\DBCO\Enum\Models\Relationship;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use Tests\Feature\FeatureTestCase;

class AlternateContactEncoderTest extends FeatureTestCase
{
    public function testEncode(): void
    {
        $hasAlternateContact = $this->faker->randomElement(YesNoUnknown::all());
        $gender = $this->faker->randomElement(Gender::all());
        $relationship = $this->faker->randomElement(Relationship::all());
        $firstname = $this->faker->optional()->firstName();
        $lastname = $this->faker->optional()->lastName();
        $phone = $this->faker->optional()->phoneNumber();
        $email = $this->faker->optional()->safeEmail();
        $isDefaultContact = $this->faker->boolean();

        $eduDaycare = AlternateContact::getSchema()->getVersion(1)->getTestFactory()->make([
            'hasAlternateContact' => $hasAlternateContact,
            'gender' => $gender,
            'relationship' => $relationship,
            'firstname' => $firstname,
            'lastname' => $lastname,
            'phone' => $phone,
            'email' => $email,
            'isDefaultContact' => $isDefaultContact,
        ]);
        $encoded = (new Encoder())->encode($eduDaycare);

        $this->assertEquals($hasAlternateContact, $encoded->hasAlternateContact);
        $this->assertEquals($gender, $encoded->gender);
        $this->assertEquals($relationship, $encoded->relationship);
        $this->assertEquals($firstname, $encoded->firstname);
        $this->assertEquals($lastname, $encoded->lastname);
        $this->assertEquals($phone, $encoded->phone);
        $this->assertEquals($email, $encoded->email);
        $this->assertEquals($isDefaultContact, $encoded->isDefaultContact);
    }
}
