<?php
namespace DBCO\HealthAuthorityAPI\Application\Models;

/**
 * Classification details.
 */
class ClassificationDetails extends AnswerValue
{
    /**
     * @var bool
     */
    public bool $category1Risk;

    /**
     * @var bool
     */
    public bool $category2ARisk;

    /**
     * @var bool
     */
    public bool $category2BRisk;

    /**
     * @var bool
     */
    public bool $category3Risk;

}
