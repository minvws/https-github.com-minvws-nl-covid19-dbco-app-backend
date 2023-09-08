<?php

declare(strict_types=1);

namespace App\Models;

class SimpleAnswer extends Answer
{
    public ?string $value;

    public function toFormValue(): array
    {
        return ['value' => $this->value];
    }

    public function fromFormValue(array $formData): void
    {
        $this->value = $formData['value'] ?? null;
    }

    public static function getValidationRules(): array
    {
        return [
            'value' => 'nullable|string',
        ];
    }
}
