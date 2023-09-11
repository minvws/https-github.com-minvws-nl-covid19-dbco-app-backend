<?php

declare(strict_types=1);

namespace App\Services\SearchHash\Normalizer;

use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;

interface Normalizer
{
    /**
     * @param Collection<array-key,string>|LazyCollection<array-key,string> $strings
     *
     * @return LazyCollection<int,string>
     */
    public function __invoke(Collection|LazyCollection $strings): LazyCollection;

    /**
     * @param Collection<array-key,string>|LazyCollection<array-key,string> $strings
     *
     * @return LazyCollection<int,string>
     */
    public function normalize(Collection|LazyCollection $strings): LazyCollection;
}
