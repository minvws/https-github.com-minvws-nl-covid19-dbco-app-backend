<?php

declare(strict_types=1);

namespace App\Helpers;

use Carbon\CarbonInterface;

class TimezoneAware
{
    public static function format(CarbonInterface $dateTime, string $format): string
    {
        return $dateTime->avoidMutation()
            ->setTimezone(Config::string('app.display_timezone'))
            ->format($format);
    }

    public static function isoFormat(CarbonInterface $dateTime, string $format): string
    {
        return $dateTime->avoidMutation()
            ->setTimezone(Config::string('app.display_timezone'))
            ->isoFormat($format);
    }
}
