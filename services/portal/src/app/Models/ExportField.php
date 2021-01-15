<?php

namespace App\Models;

class ExportField
{
    public $field;
    public $fieldLabel;
    public $value;
    public $displayValue;
    public $copyValue;
    public $isUpdated = false;

    /**
     * ExportField constructor.
     * @param String $field
     * @param String $fieldLabel
     * @param $value
     * @param null $displayValue
     * @param null $copyValue
     */
    public function __construct($field, $fieldLabel, $value, $displayValue = null, $copyValue = null)
    {
        $this->field  = $field;
        $this->fieldLabel = $fieldLabel;
        $this->value = $value;
        $this->displayValue = $displayValue ?: $value;
        $this->copyValue = $copyValue ?: $value;
    }
}
