<?php
namespace DBCO\HealthAuthorityAPI\Application\Models;

use DateTimeImmutable;

/**
 * CovidCase.
 */
class CovidCase
{
    /**
     * Case identifier.
     *
     * @var string|null
     */
    public string $uuid;

    /**
     * Date of symptom onset
     *
     * @var DateTimeImmutable|null
     */
    public ?DateTimeImmutable $dateOfSymptomOnset;
     
    /**
     * Tasks.
     *
     * @var Task[]
     */
    public array $tasks = [];
}
