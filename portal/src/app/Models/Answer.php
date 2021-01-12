<?php

namespace App\Models;

abstract class Answer
{
    public string $uuid;
    public string $taskUuid;
    public string $questionUuid;

    abstract public function isCompleted();
    abstract public function toFormValue();
    abstract public function fromFormValue(array $formData);
}
