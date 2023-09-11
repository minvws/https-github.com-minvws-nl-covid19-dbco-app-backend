<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Location\Dto;

use App\Services\Location\Dto\Location;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Unit\UnitTestCase;

class LocationTest extends UnitTestCase
{
    /**
     * @param array $overrides
     */
    #[DataProvider('completeHouseNumber')]
    public function testCompleteHouseNumber(array $overrides, string $expected): void
    {
        $location = $this->createLocation($overrides);

        $this->assertEquals($expected, $location->completeHouseNumber());
    }

    /**
     * @param array $overrides
     */
    #[DataProvider('addressLabel')]
    public function testAddressLabel(array $overrides, string $expected): void
    {
        $location = $this->createLocation($overrides);

        $this->assertEquals($expected, $location->addressLabel());
    }

    public static function completeHouseNumber(): array
    {
        return [
            'only number' => [
                'overrides' => [
                    'houseNumber' => '99',
                    'houseNumberAddition' => null,
                ],
                'expected' => '99',
            ],
            'only addition' => [
                'overrides' => [
                    'houseNumber' => null,
                    'houseNumberAddition' => 'A',
                ],
                'expected' => 'A',
            ],
            'both' => [
                'overrides' => [
                    'houseNumber' => '99',
                    'houseNumberAddition' => 'A',
                ],
                'expected' => '99 A',
            ],
            'custom addition' => [
                'overrides' => [
                    'houseNumber' => '99',
                    'houseNumberAddition' => '2 GH',
                ],
                'expected' => '99 2 GH',
            ],
        ];
    }

    public static function addressLabel(): array
    {
        return [
            'only street' => [
                'overrides' => [
                    'street' => 'Testerweg',
                    'houseNumber' => null,
                    'houseNumberAddition' => null,
                    'postalCode' => null,
                    'town' => null,
                ],
                'expected' => 'Testerweg ,',
            ],
            'only house number' => [
                'overrides' => [
                    'street' => null,
                    'houseNumber' => '99',
                    'houseNumberAddition' => null,
                    'postalCode' => null,
                    'town' => null,
                ],
                'expected' => '99,',
            ],
            'only house number with addition' => [
                'overrides' => [
                    'street' => null,
                    'houseNumber' => '99',
                    'houseNumberAddition' => 'A',
                    'postalCode' => null,
                    'town' => null,
                ],
                'expected' => '99 A,',
            ],
            'both postal code' => [
                'overrides' => [
                    'street' => null,
                    'houseNumber' => null,
                    'houseNumberAddition' => null,
                    'postalCode' => '1234AA',
                    'town' => null,
                ],
                'expected' => ', 1234AA',
            ],
            'only city' => [
                'overrides' => [
                    'street' => null,
                    'houseNumber' => null,
                    'houseNumberAddition' => null,
                    'postalCode' => null,
                    'town' => 'Duckstad',
                ],
                'expected' => ',  Duckstad',
            ],
            'all' => [
                'overrides' => [
                    'street' => 'Testerweg',
                    'houseNumber' => '99',
                    'houseNumberAddition' => 'A',
                    'postalCode' => '1234AA',
                    'town' => 'Duckstad',
                ],
                'expected' => 'Testerweg 99 A, 1234AA Duckstad',
            ],
        ];
    }

    private function createLocation(array $overrides = []): Location
    {
        $location = new Location();
        $location->id = '1';
        $location->name = 'Stadion';
        $location->street = 'Testweg';
        $location->houseNumber = '99';
        $location->houseNumberAddition = null;
        $location->town = 'Duckstad';
        $location->country = null;
        $location->postalCode = '1234AA';
        $location->type = 'adres';
        $location->sources = [];
        $location->business = false;
        $location->phone = '088 4531234';
        $location->email = 'some@email.com';
        $location->url = 'https://example.com';
        $location->kvk = null;
        $location->ggdName = 'GGD Midden Gelderland';
        $location->ggdCode = 'GG1234';
        $location->ggdTown = 'Amersfoort';
        $location->ggdMunicipality = null;
        $location->latitude = 8.123_123;
        $location->longitude = 50.1_231_298;

        foreach ($overrides as $key => $value) {
            $location->{$key} = $value;
        }

        return $location;
    }
}
