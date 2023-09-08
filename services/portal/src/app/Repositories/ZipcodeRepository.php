<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Eloquent\Zipcode;

class ZipcodeRepository
{
    public function findByZipcode(string $zipcode): ?Zipcode
    {
        return Zipcode::query()
            ->where('zipcode', $zipcode)
            ->first();
    }
}
