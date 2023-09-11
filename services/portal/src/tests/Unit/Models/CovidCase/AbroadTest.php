<?php

declare(strict_types=1);

namespace Tests\Unit\Models\CovidCase;

use App\Http\Controllers\Api\Traits\ValidatesModels;
use App\Models\CovidCase\Abroad;
use App\Models\CovidCase\Trip;
use Carbon\CarbonImmutable;
use DateTimeImmutable;
use MinVWS\Codable\Decoder;
use MinVWS\Codable\Encoder;
use MinVWS\DBCO\Enum\Models\Country;
use MinVWS\DBCO\Enum\Models\TransportationType;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

use function config;

#[Group('abroad')]
#[Group('fragment')]
final class AbroadTest extends TestCase
{
    use ValidatesModels;

    public function testEncodeDecode(): void
    {
        $trip = Trip::newInstanceWithVersion(1);
        $trip->departureDate = DateTimeImmutable::createFromFormat('!Y-m-d', CarbonImmutable::now()->subDays(10)->format('Y-m-d'));
        $trip->returnDate = DateTimeImmutable::createFromFormat('!Y-m-d', CarbonImmutable::now()->subDays(5)->format('Y-m-d'));
        $trip->countries = [Country::usa()];
        $trip->transportation = [TransportationType::plane()];

        $abroadModel = Abroad::newInstanceWithVersion(1);
        $abroadModel->wasAbroad = YesNoUnknown::yes();
        $abroadModel->trips = [$trip];

        $encoded = (new Encoder())->encode($abroadModel);
        /** @var Abroad $decoded */
        $decoded = (new Decoder())->decode($encoded)->decodeObject(Abroad::class);

        $this->assertEquals($abroadModel->jsonSerialize(), $decoded->jsonSerialize());
    }

    public function testWithInvalidPayloadShouldNotValidate(): void
    {
        $validationResult = $this->validateModel(Abroad::class, [
            'wasAbroad' => 'wrong',
            'trips' => [
                0 => [
                    'departureDate' => "wrong date",
                    'returnDate' => "wrong date",
                ]],

        ]);

        $this->assertArrayHasKey('failed', $validationResult['fatal']);
        $this->assertArrayHasKey('wasAbroad', $validationResult['fatal']['failed']);
        $this->assertArrayHasKey('trips.0.returnDate', $validationResult['fatal']['failed']);
        $this->assertArrayHasKey('DateFormat', $validationResult['fatal']['failed']['trips.0.returnDate']);
        $this->assertArrayHasKey('trips.0.departureDate', $validationResult['fatal']['failed']);
        $this->assertArrayHasKey('DateFormat', $validationResult['fatal']['failed']['trips.0.departureDate']);
    }

    public function testWithEmptyPayloadShouldValidate(): void
    {
        $validationResult = $this->validateModel(Abroad::class, []);
        $this->assertEmpty($validationResult);
    }

    public function testWithValidPayloadShouldValidate(): void
    {
        $validationResult = $this->validateModel(Abroad::class, [
            'wasAbroad' => YesNoUnknown::yes()->value,
            'trips' => [
                0 => [
                    'departureDate' => CarbonImmutable::now()->subDays(10)->format('Y-m-d'),
                    'returnDate' => CarbonImmutable::now()->subDays(5)->format('Y-m-d'),
                ]],
            'maxAbroadDepartureBeforeCaseCreation' => CarbonImmutable::now()->sub(
                config('misc.validations.maxAbroadDepartureBeforeCaseCreationInYears') . ' years',
            )->format(
                'Y-m-d',
            ),
        ]);

        $this->assertEmpty($validationResult);
    }

    public function testWithReturnDateBeforeDepartureDateShouldNotValidate(): void
    {
        $validationResult = $this->validateModel(Abroad::class, [
            'trips' => [
                0 => [
                    'departureDate' => CarbonImmutable::now()->subDays(5)->format('Y-m-d'),
                    'returnDate' => CarbonImmutable::now()->subDays(10)->format('Y-m-d'),
                ]],
        ]);

        $this->assertArrayHasKey('AfterOrEqual', $validationResult['warning']['failed']['trips.0.returnDate']);
    }
}
