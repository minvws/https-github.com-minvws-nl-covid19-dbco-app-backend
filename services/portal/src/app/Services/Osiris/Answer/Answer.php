<?php

declare(strict_types=1);

namespace App\Services\Osiris\Answer;

class Answer
{
    public function __construct(public readonly string $code, public readonly string $value)
    {
    }
}
