<?php

declare(strict_types=1);

namespace App\Services\SearchHash\Normalizer;

use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Str;

use function assert;
use function is_string;
use function preg_replace;
use function str_replace;
use function strtolower;

final class HashNormalizer implements Normalizer
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

        return $strings
            // replace non-ascii chars with ascii
            ->map(static fn (string $string): string => Str::isAscii($string) ? $string : Str::ascii($string))
            // cast to lowercase
            ->map(static fn (string $string): string => strtolower($string))
            // remove dashes, underscores and stars
            ->map(static fn (string $string): string => (string) str_replace(['-', '_', '*'], '', $string))
            // remove all whitespaces
            ->map(static fn (string $string): string => (string) preg_replace('/\s+/', '', $string))
            ->values();
    }

    public function normalizeString(string $string): string
    {
        $normalized = $this->normalize(LazyCollection::make([$string]))->first();

        assert(is_string($normalized));

        return $normalized;
    }
}
