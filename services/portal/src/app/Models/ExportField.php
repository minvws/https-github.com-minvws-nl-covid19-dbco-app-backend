<?php

namespace App\Models;

class ExportField
{
    public $value;
    public $displayValue;
    public $copyValue;
    public $isUpdated = false;

    /**
     * ExportField constructor.
     * @param $value
     * @param null $displayValue
     * @param null $copyValue
     */
    public function __construct($value, $displayValue = null, $copyValue = null)
    {
        $this->value = $value;
        $this->displayValue = $displayValue ?: $value;
        $this->copyValue = $copyValue ?: $value;
    }
}
