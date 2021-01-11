<?php

namespace App\Models;

class SimpleAnswer extends Answer
{
    public ?string $value;

    public function isCompleted(): bool
    {
        return !empty($this->value);
    }

    public function toFormValue()
    {
        return $this->value;
    }

    public function fromFormValue(?string $value): self
    {
        $answer = new self;
        $this->value = $value;
        return $answer;
    }

    public static function getValidationRules(): array
    {
        return [
            'value' => 'nullable|string'
        ];
    }
}
