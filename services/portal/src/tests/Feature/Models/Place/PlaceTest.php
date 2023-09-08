<?php

declare(strict_types=1);

namespace Tests\Feature\Models\Place;

use App\Models\Eloquent\Place;
use MinVWS\DBCO\Enum\Models\ContextCategory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

#[Group('place')]
class PlaceTest extends FeatureTestCase
{
    #[DataProvider('placeAddressProvider')]
    public function testAddressLabel(
        ?string $street,
        ?string $houseNumber,
        ?string $houseNumberSuffix,
        ?string $postalcode,
        ?string $town,
        string $completeHouseNumber,
        string $addressLabel,
    ): void {
        $place = Place::newInstanceWithVersion(Place::getSchema()->getCurrentVersion()->getVersion());
        $place->street = $street;
        $place->housenumber = $houseNumber;
        $place->housenumber_suffix = $houseNumberSuffix;
        $place->postalcode = $postalcode;
        $place->town = $town;

        $this->assertEquals($completeHouseNumber, $place->completeHouseNumber());
        $this->assertEquals($addressLabel, $place->addressLabel());
    }

    public static function placeAddressProvider(): array
    {
        return [
            'null fields' => [
                null,
                null,
                null,
                null,
                null,
                '',
                ',',
            ],
            'empty fields' => [
                '',
                '',
                '',
                '',
                '',
                '',
                ',',
            ],
            'street' => [
                'Straatnaam',
                null,
                null,
                null,
                null,
                '',
                'Straatnaam ,',
            ],
            'street+number' => [
                'Straatnaam',
                '1',
                null,
                null,
                null,
                '1',
                'Straatnaam 1,',
            ],
            'street+number+suffix' => [
                'Straatnaam',
                '1',
                'a',
                null,
                null,
                '1 a',
                'Straatnaam 1 a,',
            ],
            'street+postal' => [
                'Straatnaam',
                null,
                null,
                '1234AB',
                null,
                '',
                'Straatnaam , 1234AB',
            ],
            'street+postal+town' => [
                'Straatnaam',
                null,
                null,
                '1234AB',
                'Plaatsnaam',
                '',
                'Straatnaam , 1234AB Plaatsnaam',
            ],
            'everything' => [
                'Straatnaam',
                '1',
                'a',
                '1234AB',
                'Plaatsnaam',
                '1 a',
                'Straatnaam 1 a, 1234AB Plaatsnaam',
            ],
        ];
    }

    public function testCategoryCast(): void
    {
        $place = Place::newInstanceWithVersion(Place::getSchema()->getCurrentVersion()->getVersion());
        $place->label = $this->faker->word();
        $place->street = $this->faker->streetName;
        $place->housenumber = $this->faker->numberBetween(1, 999);
        $place->housenumber_suffix = $this->faker->randomLetter;
        $place->postalcode = $this->faker->postcode;
        $place->town = $this->faker->city;
        $place->category = ContextCategory::dagopvang();
        $place->save();

        $place->refresh();

        $this->assertEquals(ContextCategory::dagopvang(), $place->category);
    }
}
