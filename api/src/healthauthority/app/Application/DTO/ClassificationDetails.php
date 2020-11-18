<?php
declare(strict_types=1);

namespace  DBCO\HealthAuthorityAPI\Application\DTO;

use DBCO\HealthAuthorityAPI\Application\Models\ClassificationDetails as ClassificationDetailsModel;
use stdClass;

/**
 * Classification details DTO.
 *
 * @package DBCO\HealthAuthorityAPI\Application\DTO
 */
class ClassificationDetails
{
    /**
     * @var ClassificationDetailsModel $classificationDetails
     */
    private ClassificationDetailsModel $classificationDetails;

    /**
     * Constructor.
     *
     * @param ClassificationDetailsModel $classificationDetails
     */
    public function __construct(ClassificationDetailsModel $classificationDetails)
    {
        $this->classificationDetails = $classificationDetails;
    }

    /**
     * Unserialize JSON data structure.
     *
     * @param stdClass $data
     *
     * @return ClassificationDetailsModel
     */
    public static function jsonUnserialize(stdClass $data): ClassificationDetailsModel
    {
        $classificationDetails = new ClassificationDetailsModel();
        $classificationDetails->category1Risk = $data->category1Risk ?? false;
        $classificationDetails->category2ARisk = $data->category2ARisk ?? false;
        $classificationDetails->category2BRisk = $data->category2BRisk ?? false;
        $classificationDetails->category3Risk = $data->category3Risk ?? false;
        return $classificationDetails;
    }
}
