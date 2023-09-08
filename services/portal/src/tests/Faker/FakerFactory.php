<?php

declare(strict_types=1);

namespace Tests\Faker;

use Faker\Generator;
use Faker\Provider\Base;
use Tests\Faker\Provider\nl_NL\HpzoneNumber;
use Tests\Faker\Provider\nl_NL\Phonenumber;

class FakerFactory
{
    /** @var array<Base> */
    protected static array $providers = [
        Phonenumber::class,
        HpzoneNumber::class,
    ];

    public static function addProviders(Generator $faker): Generator
    {
        foreach (self::$providers as $provider) {
            $faker->addProvider(new $provider($faker));
        }

        return $faker;
    }
}
