<?php

namespace App\Models;

class SimpleAnswer extends Answer
{
    public string $value;

    public function progressContribution()
    {
        return 0;
    }

    public function toFormValue()
    {
        return $this->value;
    }
}
