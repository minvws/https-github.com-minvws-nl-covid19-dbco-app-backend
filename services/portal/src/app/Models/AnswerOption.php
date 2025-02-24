<?php

declare(strict_types=1);

namespace App\Models;

class AnswerOption
{
    public string $uuid;
    public string $label;
    public string $value;
    public ?string $trigger;
}
