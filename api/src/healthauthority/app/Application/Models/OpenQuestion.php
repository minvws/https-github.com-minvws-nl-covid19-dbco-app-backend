<?php
namespace App\Application\Models;

/**
 * Open question.
 */
class OpenQuestion extends Question
{
    /**
     * @var string
     */
    public string $questionType = 'open';
}
