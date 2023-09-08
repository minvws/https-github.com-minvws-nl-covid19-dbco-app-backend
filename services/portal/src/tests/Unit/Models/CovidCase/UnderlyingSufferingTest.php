<?php

declare(strict_types=1);

namespace Tests\Unit\Models\CovidCase;

use App\Http\Controllers\Api\Traits\ValidatesModels;
use App\Models\CovidCase\UnderlyingSuffering;
use Carbon\CarbonImmutable;
use MinVWS\Codable\Decoder;
use MinVWS\Codable\Encoder;
use MinVWS\DBCO\Enum\Models\UnderlyingSuffering as UnderlyingSufferingEnum;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

use function sprintf;

#[Group('underlying-suffering')]
#[Group('fragment')]
final class UnderlyingSufferingTest extends TestCase
{
    use ValidatesModels;

    #[Group('underlying-suffering-coding')]
    public function testEncodeDecode(): void
    {
        $underlyingSuffering = UnderlyingSuffering::newInstanceWithVersion(1);
        $underlyingSuffering->hasUnderlyingSufferingOrMedication = YesNoUnknown::yes();
        $underlyingSuffering->hasUnderlyingSuffering = YesNoUnknown::yes();
        $underlyingSuffering->items = [UnderlyingSufferingEnum::cardioVascular()];
        $underlyingSuffering->otherItems = ['other1', 'other2'];
        $underlyingSuffering->remarks = 'remarks';

        $encoded = (new Encoder())->encode($underlyingSuffering);
        $decoded = (new Decoder())->decode($encoded)->decodeObject(UnderlyingSuffering::class);

        $this->assertEquals($underlyingSuffering, $decoded);
    }

    public function testWithInvalidPayloadShouldNotValidate(): void
    {
        $validationResult = $this->validateModel(UnderlyingSuffering::class, [
            'hasUnderlyingSufferingOrMedication' => 'wrong',
            'hasUnderlyingSuffering' => 'wrong',
            'items' => [
                'unknown-suffering',
            ],
            'otherItems' => 'wrong type',
            'caseCreationDate' => CarbonImmutable::now()->format('Y-m-d'),
        ]);

        $this->assertArrayHasKey('failed', $validationResult['fatal']);

        $expectedFailingProperties = [
            'hasUnderlyingSufferingOrMedication',
            'hasUnderlyingSuffering',
            'items.0',
            'otherItems',
        ];

        foreach ($expectedFailingProperties as $expectedFailingProperty) {
            $this->assertArrayHasKey(
                $expectedFailingProperty,
                $validationResult['fatal']['failed'],
                sprintf('expected field %s to fail', $expectedFailingProperty),
            );
        }
    }

    // ----------------------------------------------------------- PORTAL 2.0 OSIRIS VALIDATIE FIX START
    // public function testWithEmptyPayloadShouldNotValidate(): void
    // {
    //     $validationResult = $this->validateModel(UnderlyingSuffering::class, [
    //         'caseCreationDate' => CarbonImmutable::now()->format('Y-m-d'),
    //     ]);
    //     $this->assertArrayHasKey('hasUnderlyingSufferingOrMedication', $validationResult['warning']['failed']);
    // }
    // ----------------------------------------------------------- PORTAL 2.0 OSIRIS VALIDATIE FIX END

    public function testWithValidPayloadShouldValidate(): void
    {
        $validationResult = $this->validateModel(UnderlyingSuffering::class, [
            'hasUnderlyingSufferingOrMedication' => 'yes',
            'hasUnderlyingSuffering' => 'yes',
            'items' => $this->faker->randomElements(
                UnderlyingSufferingEnum::getCurrentVersion()->allValues(),
            ),
            'otherItems' => ['stress'],
            'caseCreationDate' => CarbonImmutable::now()->format('Y-m-d'),
        ]);

        $this->assertEmpty($validationResult);
    }
}
