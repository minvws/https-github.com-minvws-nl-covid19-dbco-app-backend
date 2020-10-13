<?php
namespace App\Application\Models;

/**
 * Task.
 */
class Task
{
    /**
     * @var string
     */
    public string $id;

    /**
     * @var string
     */
    public string $taskType;

    /**
     * @var string
     */
    public string $source;

    /**
     * @var string
     */
    public string $label;

    /**
     * @var string
     */
    public string $context;

    /**
     * @var string
     */
    public string $category;

    /**
     * @var string
     */
    public string $communication;
}
