<?php

declare(strict_types=1);

namespace App\Dto\TestResultReport;

use InvalidArgumentException;

use function in_array;
use function sprintf;

final class Source
{
    private const CORONIT = 'CoronIT';
    private const MELDPORTAAL = 'MeldPortaal';

    private string $value;

    public function __construct(string $value)
    {
        if (!in_array($value, [self::CORONIT, self::MELDPORTAAL], true)) {
            throw new InvalidArgumentException(sprintf('Invalid argument given for source: "%s"', $value));
        }

        $this->value = $value;
    }

    public function isCoronit(): bool
    {
        return $this->value === self::CORONIT;
    }

    public function isMeldportaal(): bool
    {
        return $this->value === self::MELDPORTAAL;
    }

    public function toString(): string
    {
        return $this->value;
    }
}
