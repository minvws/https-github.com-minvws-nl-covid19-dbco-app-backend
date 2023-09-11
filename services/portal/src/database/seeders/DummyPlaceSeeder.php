<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Eloquent\Place;
use App\Models\Eloquent\Section;
use Illuminate\Database\Seeder;
use MinVWS\DBCO\Enum\Models\ContextCategory;

class DummyPlaceSeeder extends Seeder
{
    /**
     * Add example places for contexts.
     */
    public function run(): void
    {
        // Data from example controller
        $places = [
            [
                "label" => "Tennisvereniging Volley",
                "indexCount" => 2, // The number of indexes that has this place in their contexts in the past 28 days.
                "addressLabel" => "Kleiweg 500, 3045 PM Rotterdam",
                "street" => "Kleiweg",
                "houseNumber" => "500",
                "postalcode" => "3045PM",
                "town" => "Rotterdam",
                "category" => ContextCategory::verenigingOverige(),
            ],
            [
                "label" => "Tennisvereniging Kraycheck",
                "indexCount" => 1,
                "addressLabel" => "Kleiweg 500, 3045 PM Rotterdam",
                "street" => "Kleiweg",
                "houseNumber" => "500",
                "postalcode" => "3045PM",
                "town" => "Rotterdam",
                "category" => ContextCategory::verenigingOverige(),
            ],
            [
                "label" => "Tennisdokter Kraycheck",
                "indexCount" => 0,
                "addressLabel" => "Dokterweg 500, 3045 PM Rotterdam",
                "street" => "Kleiweg",
                "houseNumber" => "500",
                "postalcode" => "3045PM",
                "town" => "Rotterdam",
                "category" => ContextCategory::ziekenhuis(),
            ],
        ];

        $sections = [
            'Receptie',
            'Kantine',
            'Fietsenhok',
        ];

        $numberOfSections = 0;
        foreach ($places as $placeData) {
            $place = new Place();
            $place->label = $placeData['label'];
            $place->category = $placeData['category'];
            $place->street = $placeData['street'];
            $place->housenumber = $placeData['houseNumber'];
            $place->postalcode = $placeData['postalcode'];
            $place->town = $placeData['town'];
            $place->country = 'NL';
            $place->save();

            for ($i = 0; $i <= $numberOfSections; $i++) {
                $section = new Section();
                $section->place_uuid = $place->uuid;
                $section->label = $sections[$i];
                $section->save();
            }
            $numberOfSections++;
        }
    }
}
