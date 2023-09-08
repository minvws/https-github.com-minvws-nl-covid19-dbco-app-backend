<?php

declare(strict_types=1);

namespace App\Dto\TestResultReport;

use DateTimeImmutable;
use DateTimeInterface;
use RuntimeException;

final class Triage
{
    public ?DateTimeInterface $dateOfFirstSymptom = null;

    private function __construct()
    {
    }

    public static function fromArray(array $array): self
    {
        $self = new Triage();

        if ($array['dateOfFirstSymptom'] !== null) {
            $dateOfFirstSymptom = DateTimeImmutable::createFromFormat('m-d-Y', $array['dateOfFirstSymptom']);

            $self->dateOfFirstSymptom = $dateOfFirstSymptom !== false ? $dateOfFirstSymptom
                : throw new RuntimeException('Failed to parse dateOfFirstSymptom');
        }

        return $self;
    }
}
