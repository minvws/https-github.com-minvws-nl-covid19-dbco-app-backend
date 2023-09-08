<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Eloquent\Person;
use Illuminate\Database\Eloquent\Collection;

class PersonRepository
{
    /**
     * @return Collection<int, Person>
     */
    public function getPersonsWithUnencryptedDateOfBirth(int $limit): Collection
    {
        return Person::query()
            ->whereNull('date_of_birth_encrypted')
            ->limit($limit)
            ->get();
    }
}
