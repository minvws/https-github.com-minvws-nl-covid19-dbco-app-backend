<?php

declare(strict_types=1);

namespace Tests\Unit\Models\CovidCase;

use App\Http\Controllers\Api\Traits\ValidatesModels;
use App\Models\CovidCase\GeneralPractitioner;
use MinVWS\Codable\Decoder;
use MinVWS\Codable\Encoder;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('fragment')]
final class GeneralPractitionerTest extends TestCase
{
    use ValidatesModels;

    public function testEncodeDecode(): void
    {
        /** @var GeneralPractitioner $generalPractitioner */
        $generalPractitioner = GeneralPractitioner::getSchema()->getVersion(1)->newInstance();
        $generalPractitioner->name = "Dr Strange";
        $generalPractitioner->practiceName = "Avengers";

        $generalPractitioner->address = $generalPractitioner->getSchemaVersion()->getField('address')->newInstance();
        $generalPractitioner->address->houseNumber = "23";
        $generalPractitioner->address->postalCode = "1234";
        $generalPractitioner->address->houseNumberSuffix = "pre";
        $generalPractitioner->address->street = "Magic Avenue";
        $generalPractitioner->address->town = "Ghost Town";

        $generalPractitioner->hasInfectionNotificationConsent = true;

        $encoded = (new Encoder())->encode($generalPractitioner);

        /** @var GeneralPractitioner $decoded */
        $decoded = (new Decoder())->decode($encoded)->decodeObject(GeneralPractitioner::class);

        $this->assertEquals($generalPractitioner->jsonSerialize(), $decoded->jsonSerialize());
    }

    public function testWithInvalidPayloadShouldNotValidate(): void
    {
        $validationResult = $this->validateModel(GeneralPractitioner::class, [
            'name' => 123,
            'practiceName' => 123,
            'address' => [
                'postalCode' => 123,
                'houseNumber' => 123,
                'houseNumberSuffix' => 123,
                'street' => 123,
                'town' => 123,
            ],
            'hasInfectionNotificationConsent' => "wrong",
        ]);

        $this->assertArrayHasKey('failed', $validationResult['fatal']);
        $this->assertArrayHasKey('name', $validationResult['fatal']['failed']);
        $this->assertArrayHasKey('practiceName', $validationResult['fatal']['failed']);
        $this->assertArrayHasKey('address.postalCode', $validationResult['fatal']['failed']);
        $this->assertArrayHasKey('address.houseNumber', $validationResult['fatal']['failed']);
        $this->assertArrayHasKey('address.houseNumberSuffix', $validationResult['fatal']['failed']);
        $this->assertArrayHasKey('address.street', $validationResult['fatal']['failed']);
        $this->assertArrayHasKey('address.town', $validationResult['fatal']['failed']);
        $this->assertArrayHasKey('hasInfectionNotificationConsent', $validationResult['fatal']['failed']);
    }

    public function testWithEmptyPayloadShouldValidate(): void
    {
        $validationResult = $this->validateModel(GeneralPractitioner::class, []);
        $this->assertEmpty($validationResult);
    }

    public function testWithValidPayloadShouldValidate(): void
    {
        $validationResult = $this->validateModel(GeneralPractitioner::class, [
            'name' => 'Dr Strange',
            'practiceName' => 'Avengers',
            'address' => [
                'postalCode' => '1234AA',
                'houseNumber' => '23',
                'houseNumberSuffix' => 'pre',
                'street' => 'Magic Avenue',
                'town' => 'Ghost Town',
            ],
            'hasInfectionNotificationConsent' => true,
        ]);

        $this->assertEmpty($validationResult);
    }
}
