<?php

declare(strict_types=1);

namespace Tests\Unit\Models\Task;

use App\Http\Controllers\Api\Traits\ValidatesModels;
use App\Models\Task\Symptoms;
use DateTimeImmutable;
use MinVWS\Codable\Decoder;
use MinVWS\Codable\Encoder;
use MinVWS\DBCO\Enum\Models\Symptom;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use Tests\TestCase;

use function sprintf;

final class SymptomsTest extends TestCase
{
    use ValidatesModels;

    public function testEncodeDecode(): void
    {
        /** @var Symptoms $expectedSymptoms */
        $expectedSymptoms = Symptoms::newInstanceWithVersion(1);
        $expectedSymptoms->hasSymptoms = YesNoUnknown::yes();
        $expectedSymptoms->otherSymptoms = ['zenuwachtig'];
        $expectedSymptoms->symptoms = [Symptom::malaise()];
        $expectedSymptoms->dateOfSymptomOnset = DateTimeImmutable::createFromFormat('!Y-m-d', '2020-11-01');

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
            'dateOfSymptomOnset' => 123,
        ]);

        $this->assertArrayHasKey('failed', $validationResult['fatal']);

        $expectedFailingProperties = [
            'hasSymptoms',
            'symptoms.0',
            'otherSymptoms',
            'dateOfSymptomOnset',
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
            'dateOfSymptomOnset' => '2020-11-01',
        ]);

        $this->assertEmpty($validationResult);
    }
}
