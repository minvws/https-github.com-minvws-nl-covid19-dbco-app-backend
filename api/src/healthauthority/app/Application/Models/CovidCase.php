<?php
namespace DBCO\HealthAuthorityAPI\Application\Models;

use DateTimeInterface;

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
     * @var DateTimeInterface|null
     */
    public ?DateTimeInterface $dateOfSymptomOnset;

    /**
     * Date case expires for input.
     *
     * @var DateTimeInterface|null
     */
    public DateTimeInterface $windowExpiresAt;

    /**
     * Tasks.
     *
     * @var Task[]
     */
    public array $tasks = [];
}
