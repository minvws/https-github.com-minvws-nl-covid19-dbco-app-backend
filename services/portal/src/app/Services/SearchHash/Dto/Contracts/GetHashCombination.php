<?php

declare(strict_types=1);

namespace App\Services\SearchHash\Dto\Contracts;

use Illuminate\Support\Collection;

interface GetHashCombination
{
    /**
     * @return Collection<int,string>
     */
    public function getHashCombination(): Collection;
}
