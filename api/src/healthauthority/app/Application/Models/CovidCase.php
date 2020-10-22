<?php
namespace DBCO\HealthAuthorityAPI\Application\Models;

/**
 * CovidCase.
 */
class CovidCase
{
    /**
     * Date of symptom onset
     *
     * @var $dateOfSymptomOnset
     */
    public string $dateOfSymptomOnset;
     
    /**
     * Tasks.
     *
     * @var Task[]
     */
    public array $tasks = [];
}
