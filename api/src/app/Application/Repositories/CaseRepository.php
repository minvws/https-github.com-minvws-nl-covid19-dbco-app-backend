<?php
declare(strict_types=1);

namespace App\Application\Repositories;

use App\Application\Models\DbcoCase;

interface CaseRepository
{
    public function create(): DbcoCase;
}
