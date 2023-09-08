<?php

declare(strict_types=1);

namespace Tests\Helpers;

use Faker\Generator as Faker;
use Illuminate\Support\Str;

use function app;
use function method_exists;

abstract class AbstractFragmentDataFaker
{
    protected static Faker $faker;

    public static function createDataForType(string $type): array
    {
        $intakeFragmentDataMethod = 'createRandom' . Str::ucfirst($type) . 'Data';
        if (!method_exists(static::class, $intakeFragmentDataMethod)) {
            return [];
        }
        return static::$intakeFragmentDataMethod();
    }

    protected static function getFaker(): Faker
    {
        if (!isset(static::$faker)) {
            static::$faker = app(Faker::class);
        }
        return static::$faker;
    }
}
