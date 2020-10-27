<?php

namespace App\Repositories;

use Illuminate\Support\Collection;

interface AnswerRepository
{
    public function getAllAnswersByCase(string $caseUuid): Collection;
}
