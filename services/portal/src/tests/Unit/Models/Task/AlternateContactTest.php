<?php

declare(strict_types=1);

namespace Tests\Unit\Models\Task;

use App\Http\Controllers\Api\Traits\ValidatesModels;
use App\Models\Task\AlternateContact;
use MinVWS\Codable\Decoder;
use MinVWS\Codable\Encoder;
use MinVWS\DBCO\Enum\Models\Gender;
use MinVWS\DBCO\Enum\Models\Relationship;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use Tests\TestCase;

use function sprintf;

final class AlternateContactTest extends TestCase
{
    use ValidatesModels;

    public function testEncodeDecode(): void
    {
        $alternateContact = AlternateContact::newInstanceWithVersion(1);
        $alternateContact->hasAlternateContact = YesNoUnknown::yes();
        $alternateContact->lastname = "Hunt";
        $alternateContact->firstname = "Ethan";
        $alternateContact->gender = Gender::other();
        $alternateContact->relationship = Relationship::partner();
        $alternateContact->explanation = "Mission Impossible";

        $encoded = (new Encoder())->encode($alternateContact);
        $decoded = (new Decoder())->decode($encoded)->decodeObject(AlternateContact::class);

        $this->assertEquals($alternateContact, $decoded);
    }

    public function testWithInvalidPayloadShouldNotValidate(): void
    {
        $validationResult = $this->validateModel(AlternateContact::class, [
            'hasAlternateContact' => 'wrong',
            'lastname' => 123,
            'firstname' => 123,
            'gender' => 'unknown',
            'relationship' => 'enemy',
            'explanation' => 123,
        ]);

        $this->assertArrayHasKey('failed', $validationResult['fatal']);

        $expectedFailingProperties = [
            'hasAlternateContact',
            'lastname',
            'firstname',
            'gender',
            'relationship',
            'explanation',
        ];
        foreach ($expectedFailingProperties as $expectedFailingProperty) {
            $this->assertArrayHasKey(
                $expectedFailingProperty,
                $validationResult['fatal']['failed'],
                sprintf('expected field %s to fail', $expectedFailingProperty),
            );
        }
    }

    public function testWithEmptyPayloadShouldValidate(): void
    {
        $validationResult = $this->validateModel(AlternateContact::class, []);
        $this->assertEmpty($validationResult);
    }

    public function testWithValidPayloadShouldValidate(): void
    {
        $validationResult = $this->validateModel(AlternateContact::class, [
            'hasAlternateContact' => YesNoUnknown::yes()->value,
            'lastname' => 'Hunt',
            'firstname' => 'Ethan',
            'gender' => Gender::male()->value,
            'relationship' => Relationship::partner()->value,
            'explanation' => 'Mission Impossible',
        ]);

        $this->assertEmpty($validationResult);
    }
}
