<?php

declare(strict_types=1);

namespace Tests\Unit\Models\CovidCase;

use App\Http\Controllers\Api\Traits\ValidatesModels;
use App\Models\CovidCase\Symptoms;
use App\Models\Versions\CovidCase\Symptoms\SymptomsV1;
use MinVWS\Codable\Decoder;
use MinVWS\Codable\Encoder;
use MinVWS\DBCO\Enum\Models\Symptom;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('fragment')]
final class SymptomsTest extends TestCase
{
    use ValidatesModels;

    public function testEncodeDecode(): void
    {
        /** @var SymptomsV1 $expectedSymptoms */
        $expectedSymptoms = Symptoms::getSchema()->getVersion(1)->newInstance();
        $expectedSymptoms->hasSymptoms = YesNoUnknown::yes();
        $expectedSymptoms->diseaseCourse = "disease course";
        $expectedSymptoms->otherSymptoms = ['zenuwachtig'];
        $expectedSymptoms->symptoms = [Symptom::malaise()];

        $encoded = (new Encoder())->encode($expectedSymptoms);
        $decoded = (new Decoder())->decode($encoded)->decodeObject(Symptoms::class);

        $this->assertEquals($expectedSymptoms, $decoded);
    }

    public function testWithInvalidPayloadShouldNotValidate(): void
    {
        $validationResult = $this->validateModel(Symptoms::class, [
            'hasSymptoms' => 'wrong',
            'symptoms' => [
                'unknown-disease',
            ],
            'otherSymptoms' => 'wrong type',
            'diseaseCourse' => false,
        ]);

        $this->assertArrayHasKey('failed', $validationResult['fatal']);
        $this->assertArrayHasKey('hasSymptoms', $validationResult['fatal']['failed']);
        $this->assertArrayHasKey('symptoms.0', $validationResult['fatal']['failed']);
        $this->assertArrayHasKey('otherSymptoms', $validationResult['fatal']['failed']);
        $this->assertArrayHasKey('diseaseCourse', $validationResult['fatal']['failed']);
    }

    public function testWithEmptyPayloadShouldValidate(): void
    {
        $validationResult = $this->validateModel(Symptoms::class, []);
        $this->assertEmpty($validationResult);
    }

    public function testWithValidPayloadShouldValidate(): void
    {
        $validationResult = $this->validateModel(Symptoms::class, [
            'hasSymptoms' => 'yes',
            'symptoms' => [
                'malaise',
            ],
            'otherSymptoms' => ['stress'],
            'wasSymptomaticAtTimeOfCall' => YesNoUnknown::no()->value,
            'stillHadSymptomsAt' => '2021-10-01',
            'diseaseCourse' => 'sos',
        ]);

        $this->assertEmpty($validationResult);
    }
}
