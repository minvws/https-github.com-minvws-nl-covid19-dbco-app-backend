<?php
namespace App\Application\Models;

/**
 * Question.
 */
class Question
{
    /**
     * @var string
     */
    public string $id;

    /**
     * @var string
     */
    public string $group;

    /**
     * @var string
     */
    public string $questionType;

    /**
     * @var string
     */
    public string $label;

    /**
     * @var string|null
     */
    public ?string $description;

    /**
     * @var string[]
     */
    public array $relevantForCategories;
}
