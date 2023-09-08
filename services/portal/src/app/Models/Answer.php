<?php

declare(strict_types=1);

namespace App\Models;

abstract class Answer
{
    public string $uuid;
    public string $taskUuid;
    public string $questionUuid;

    abstract public function toFormValue(): array;

    abstract public function fromFormValue(array $formData): void;
}
