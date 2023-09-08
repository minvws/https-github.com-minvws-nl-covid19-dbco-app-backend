<?php

declare(strict_types=1);

namespace Database\Factories\Faker;

use Faker;

use function count;
use function min;

class RandomSample extends Faker\Provider\Base
{
    public function randomSample(array $fields, int $minimum): array
    {
        return $fields
            ? $this->generator->randomElements($fields, $this->generator->numberBetween(1, min($minimum, count($fields))))
            : [];
    }
}
