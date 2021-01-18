<?php

namespace App\Models;

class SimpleAnswer extends Answer
{
    public ?string $value;

    public function isCompleted(): bool
    {
        return !empty($this->value);
    }

    public function toFormValue(): array
    {
        return ['value' => $this->value];
    }

    public function fromFormValue(array $formData)
    {
        $this->value = $formData['value'] ?? null;
    }

    public static function getValidationRules(): array
    {
        return [
            'value' => 'nullable|string'
        ];
    }

    public static function createFromFormValue(?string $value): self
    {
        $answer = new self;
        $answer->fromFormValue($value);
        return $answer;
    }
}
