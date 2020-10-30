<?php
namespace DBCO\HealthAuthorityAPI\Application\Models;

use DateTimeImmutable;

/**
 * CovidCase.
 */
class CovidCase
{
    /**
     * Date of symptom onset
     *
     * @var DateTimeImmutable
     */
    public DateTimeImmutable $dateOfSymptomOnset;
     
    /**
     * Tasks.
     *
     * @var Task[]
     */
    public array $tasks = [];
}
