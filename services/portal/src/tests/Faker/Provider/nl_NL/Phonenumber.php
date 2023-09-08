<?php

declare(strict_types=1);

namespace Tests\Faker\Provider\nl_NL;

use Faker\Provider\Base;

class Phonenumber extends Base
{
    /**
     * $this->faker->phonenumber generates invalid phonenumbers (if validatied by propaganistas/laravel-phone),
     * therefore we provide a set of fixed valid phone numbers here
     */
    public function phonenumber(): string
    {
        return $this->generator->randomElement([
            '06 12345678',
            '+31 612345678',
            '055 1234567',
            '040 2213324',
            '+31 71 1880731',
            '071 1880731',
        ]);
    }

    public function e164PhoneNumber(): string
    {
        return $this->generator->randomElement([
            '+31612345678',
            '+31612345678',
            '+31551234567',
            '+31402213324',
            '+31711880731',
            '+31711880731',
        ]);
    }
}
