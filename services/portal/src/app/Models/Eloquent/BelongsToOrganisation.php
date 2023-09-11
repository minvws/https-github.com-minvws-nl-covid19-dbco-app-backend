<?php

declare(strict_types=1);

namespace App\Models\Eloquent;

interface BelongsToOrganisation
{
    public function getOrganisation(): ?EloquentOrganisation;
}
