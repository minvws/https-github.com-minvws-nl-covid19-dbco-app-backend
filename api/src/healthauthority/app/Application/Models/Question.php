<?php
namespace App\Application\Models;

/**
 * Question.
 */
class Question
{
    const CATEGORY_1  = '1';
    const CATEGORY_2A = '2a';
    const CATEGORY_2B = '2b';
    const CATEGORY_3  = '3';

    const ALL_CATEGORIES = [self::CATEGORY_1, self::CATEGORY_2A, self::CATEGORY_2B, self::CATEGORY_3];

    /**
     * @var string
     */
    public string $uuid;

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
