<?php

declare(strict_types=1);

namespace App\Models;

class ClassificationDetailsAnswer extends Answer
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
            'value' => 'nullable|in:1,2a,2b,3a,3b',
        ];
    }
}
