<?php
namespace App\Application\Models;

/**
 * Questionnaire.
 */
class Questionnaire
{
    /**
     * @var string
     */
    public string $uuid;

    /**
     * @var string
     */
    public string $taskType;

    /**
     * @var Question[]
     */
    public array $questions;
}
