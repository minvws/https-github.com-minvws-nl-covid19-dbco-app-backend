<?php
namespace  DBCO\HealthAuthorityAPI\Application\Models;

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
