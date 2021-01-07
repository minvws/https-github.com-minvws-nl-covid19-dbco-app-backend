<?php
namespace DBCO\HealthAuthorityAPI\Application\Models;

use DateTimeInterface;

/**
 * Answer
 */
class Answer
{
    /**
     * @var string
     */
    public string $uuid;

    /**
     * @var string
     */
    public string $questionUuid;

    /**
     * @var DateTimeInterface
     */
    public DateTimeInterface $lastModified;

    /**
     * @var AnswerValue
     */
    public AnswerValue $value;
}
