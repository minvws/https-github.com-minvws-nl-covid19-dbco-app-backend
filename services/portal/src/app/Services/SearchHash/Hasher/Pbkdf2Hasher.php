<?php

declare(strict_types=1);

namespace App\Services\SearchHash\Hasher;

use MelchiorKokernoot\LaravelAutowireConfig\Config\Config;

use function hash_pbkdf2;

final class Pbkdf2Hasher implements Hasher
{
    public function __construct(
        #[Config('searchhash.salt')]
        private readonly string $salt,
        #[Config('searchhash.iterations')]
        private readonly int $hashIterations,
        #[Config('searchhash.pbkdf2.algo')]
        private readonly string $hashAlgo,
    ) {
    }

    /**
     * @phpstan-param non-empty-string $value
     *
     * @phpstan-return non-empty-string
     */
    public function hash(string $value): string
    {
        return hash_pbkdf2($this->hashAlgo, $value, $this->salt, $this->hashIterations);
    }
}
