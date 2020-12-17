<?php

namespace App\Models;

class SimpleAnswer extends Answer
{
    public string $value;

    public function isCompleted(): bool
    {
        return !empty($this->value);
    }

    public function isContactable(): bool
    {
        return !empty($this->value);
    }

    public function toFormValue()
    {
        return $this->value;
    }
}
