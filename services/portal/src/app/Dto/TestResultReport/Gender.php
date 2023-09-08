<?php

declare(strict_types=1);

namespace App\Dto\TestResultReport;

use InvalidArgumentException;

use function in_array;
use function sprintf;

class Gender
{
    private const MALE = 'MAN';
    private const FEMALE = 'VROUW';
    private const NOT_SPECIFIED = 'NIET_GESPECIFICEERD';
    private const UNKNOWN = 'ONBEKEND';

    private string $value;

    public function __construct(string $value)
    {
        if (!in_array($value, [self::MALE, self::FEMALE, self::NOT_SPECIFIED, self::UNKNOWN], true)) {
            throw new InvalidArgumentException(sprintf('Invalid argument "%s" given', $value));
        }

        $this->value = $value;
    }

    public function isMale(): bool
    {
        return $this->value === self::MALE;
    }

    public function isFemale(): bool
    {
        return $this->value === self::FEMALE;
    }

    public function isNotSpecified(): bool
    {
        return $this->value === self::NOT_SPECIFIED;
    }

    public function isUnknown(): bool
    {
        return $this->value === self::UNKNOWN;
    }
}
