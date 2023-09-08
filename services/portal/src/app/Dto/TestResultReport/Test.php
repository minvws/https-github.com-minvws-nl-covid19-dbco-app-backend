<?php

declare(strict_types=1);

namespace App\Dto\TestResultReport;

use DateTimeImmutable;
use DateTimeInterface;

final class Test
{
    public DateTimeInterface $sampleDate;
    public DateTimeInterface $resultDate;
    public ?string $sampleLocation;
    public string $sampleId;
    public ?TypeOfTest $typeOfTest;
    public string $result;
    public Source $source;
    public ?string $testLocation;
    public ?string $testLocationCategory;

    private function __construct()
    {
    }

    public static function fromArray(array $array): self
    {
        $self = new Test();

        $self->resultDate = new DateTimeImmutable($array['resultDate']);
        $self->sampleDate = new DateTimeImmutable($array['sampleDate']);
        $self->sampleLocation = $array['sampleLocation'];
        $self->sampleId = $array['sampleId'];
        $self->typeOfTest = $array['typeOfTest'] !== null ? TypeOfTest::from($array['typeOfTest']) : null;
        $self->result = $array['result'];
        $self->source = new Source($array['source']);
        $self->testLocation = $array['testLocation'];
        $self->testLocationCategory = $array['testLocationCategory'];

        return $self;
    }
}
