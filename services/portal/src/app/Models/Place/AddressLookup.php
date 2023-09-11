<?php

declare(strict_types=1);

namespace App\Models\Place;

use App\Models\CovidCase\Contracts\Validatable;
use MinVWS\Codable\Codable;
use MinVWS\Codable\CodingKeys;

class AddressLookup implements Codable, Validatable
{
    use CodingKeys;

    /**
     * @inheritDoc
     */
    public static function validationRules(array $data): array
    {
        $rules = [];

        $rules[self::SEVERITY_LEVEL_WARNING] = [
            'postalCode' => 'nullable|string|postal_code:NL',
            'houseNumber' => 'nullable|numeric',
        ];

        return $rules;
    }
}
