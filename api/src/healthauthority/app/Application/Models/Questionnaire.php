<?php
namespace DBCO\HealthAuthorityAPI\Application\Models;

/**
 * Questionnaire.
 */
class Questionnaire
{
    public string $uuid;

    public string $name;

    public string $taskType;

    public int $version;

    /**
     * @var Question[]
     */
    public array $questions = [];
}
