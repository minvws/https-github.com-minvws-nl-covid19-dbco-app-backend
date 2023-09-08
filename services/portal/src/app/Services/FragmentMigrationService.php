<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\FragmentMigration\Builder;
use App\Services\FragmentMigration\OwnerType;
use Illuminate\Support\Facades\App;
use RuntimeException;
use Throwable;

class FragmentMigrationService
{
    public function covidCase(array $fragments): Builder
    {
        return $this->makeBuilder(OwnerType::covidCase(), $fragments);
    }

    public function task(array $fragments): Builder
    {
        return $this->makeBuilder(OwnerType::task(), $fragments);
    }

    public function context(array $fragments): Builder
    {
        return $this->makeBuilder(OwnerType::context(), $fragments);
    }

    private function makeBuilder(OwnerType $type, array $fragments): Builder
    {
        try {
            return App::make(Builder::class, [
                'fragments' => $fragments,
                'type' => $type,
            ]);
        } catch (Throwable $e) {
            throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
