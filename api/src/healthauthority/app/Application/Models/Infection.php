<?php
namespace App\Application\Models;

/**
 * Infection.
 */
class Infection
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
