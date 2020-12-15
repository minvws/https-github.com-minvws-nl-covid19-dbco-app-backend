<?php

namespace App\Models;

class SimpleAnswer extends Answer
{
    public string $value;

    public function progressContribution(): bool
    {
        return false;
    }

    public function toFormValue()
    {
        return $this->value;
    }
}
