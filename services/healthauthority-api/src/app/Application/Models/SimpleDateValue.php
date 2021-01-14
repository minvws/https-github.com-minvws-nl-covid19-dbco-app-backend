<?php
namespace DBCO\HealthAuthorityAPI\Application\Models;


use DateTimeInterface;

/**
 * Simple date value.
 */
class SimpleDateValue extends AnswerValue
{
    /**
     * @var DateTimeInterface|null
     */
    public ?DateTimeInterface $value;
}
