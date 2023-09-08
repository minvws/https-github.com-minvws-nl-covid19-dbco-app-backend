<?php

declare(strict_types=1);

namespace App\Dto\Osiris;

class CaseUnansweredQuestionsResult
{
    private array $errors;

    private function __construct(array $errors)
    {
        $this->errors = $errors;
    }

    public static function create(array $errors): self
    {
        return new self($errors);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function isValid(): bool
    {
        return empty($this->errors);
    }
}
