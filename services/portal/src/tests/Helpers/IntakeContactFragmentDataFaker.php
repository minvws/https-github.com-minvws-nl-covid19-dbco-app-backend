<?php

declare(strict_types=1);

namespace Tests\Helpers;

class IntakeContactFragmentDataFaker extends AbstractFragmentDataFaker
{
    public static function createRandomGeneralData(): array
    {
        return [
            'isSource' => static::getFaker()->boolean(),
            'reference' => (string) static::getFaker()->unique()->numberBetween(1_000_000, 9_999_999),
        ];
    }
}
