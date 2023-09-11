<?php

declare(strict_types=1);

namespace Tests\Helpers;

use Faker\Generator;

use function array_map;
use function is_array;

class FakerHelper
{
    public static function populateWithDateTimes(Generator $faker, array $data): array
    {
        return array_map(static function ($d) use ($faker) {
            return is_array($d) ? $faker->dateTimeBetween($d[0], $d[1])
                ->format('Y-m-d') : $faker->dateTime($d)->format('Y-m-d');
        }, $data);
    }

    public static function getPastDateAfter(string $start): array
    {
        return [$start, 'now'];
    }

    public static function getDate(string $date): array
    {
        return [$date, $date];
    }

    public static function getDateBefore(string $before): array
    {
        return ['-200 years', $before];
    }

    public static function getDateBetween(string $start, ?string $end = null): array
    {
        return [$start, $end];
    }
}
