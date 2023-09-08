<?php

declare(strict_types=1);

namespace App\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;

interface Builder
{
    /**
     * Build answer(s) for the given case.
     *
     * Should return zero or more answers to be included in the Osiris response.
     *
     * @return array<Answer>
     */
    public function build(EloquentCase $case): array;
}
