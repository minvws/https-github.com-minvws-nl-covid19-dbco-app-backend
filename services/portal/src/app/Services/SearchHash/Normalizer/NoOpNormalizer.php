<?php

declare(strict_types=1);

namespace App\Services\SearchHash\Normalizer;

use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;

final class NoOpNormalizer implements Normalizer
{
    /**
     * @param Collection<array-key,string>|LazyCollection<array-key,string> $strings
     */
    public function __invoke(Collection|LazyCollection $strings): LazyCollection
    {
        return $this->normalize($strings);
    }

    /**
     * @param Collection<array-key,string>|LazyCollection<array-key,string> $strings
     */
    public function normalize(Collection|LazyCollection $strings): LazyCollection
    {
        if ($strings instanceof Collection) {
            $strings = $strings->lazy();
        }

        return $strings;
    }
}
