<?php
declare(strict_types=1);

namespace  DBCO\HealthAuthorityAPI\Application\DTO;

use DBCO\HealthAuthorityAPI\Application\Models\SimpleValue as SimpleValueModel;
use stdClass;

/**
 * Simple value DTO.
 *
 * @package DBCO\HealthAuthorityAPI\Application\DTO
 */
class SimpleValue
{
    /**
     * @var SimpleValueModel $simpleValue
     */
    private SimpleValueModel $simpleValue;

    /**
     * Constructor.
     *
     * @param SimpleValueModel $simpleValue
     */
    public function __construct(SimpleValueModel $simpleValue)
    {
        $this->simpleValue = $simpleValue;
    }

    /**
     * Unserialize JSON data structure.
     *
     * @param stdClass $data
     *
     * @return SimpleValueModel
     */
    public static function jsonUnserialize(stdClass $data): SimpleValueModel
    {
        $simpleValue = new SimpleValueModel();
        $simpleValue->value = $data->value ?? '';
        return $simpleValue;
    }
}
