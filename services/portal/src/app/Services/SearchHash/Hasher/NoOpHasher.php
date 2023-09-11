<?php

declare(strict_types=1);

namespace App\Services\SearchHash\Hasher;

final class NoOpHasher implements Hasher
{
    /**
     * @phpstan-param non-empty-string $value
     *
     * @phpstan-return non-empty-string
     */
    public function hash(string $value): string
    {
        return $value;
    }
}
