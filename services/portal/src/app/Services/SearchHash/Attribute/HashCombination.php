<?php

declare(strict_types=1);

namespace App\Services\SearchHash\Attribute;

use App\Services\SearchHash\Dto\Contracts\GetHashCombination;
use App\Services\SearchHash\Dto\Contracts\GetHashKeyName;
use App\Services\SearchHash\Exception\SearchHashInvalidArgumentException;
use Attribute;
use Illuminate\Support\Collection;

use function array_filter;
use function array_map;
use function assert;
use function count;
use function implode;
use function sort;
use function trim;

use const SORT_FLAG_CASE;
use const SORT_NATURAL;

#[Attribute(Attribute::TARGET_METHOD)]
final class HashCombination implements GetHashKeyName, GetHashCombination
{
    /** @var Collection<int,string> */
    protected readonly Collection $hashCombination;

    protected readonly string $keyName;
    protected string $hashMethod;

    public function __construct(string ...$keys)
    {
        assert(count($keys) > 0, new SearchHashInvalidArgumentException('Expected atleast 1 key.'));

        $keys = array_map(static fn (string $key): string => trim($key), $keys);

        assert(
            count(array_filter($keys)) === count($keys),
            new SearchHashInvalidArgumentException('Expected non-empty-string key values.'),
        );

        sort($keys, SORT_FLAG_CASE | SORT_NATURAL);

        /** @var Collection<int,string> $hashCombination */
        $hashCombination = Collection::make($keys);

        $this->hashCombination = $hashCombination;
        $this->keyName = $this->generateHashKeyName();
    }

    public function getHashKeyName(): string
    {
        return $this->keyName;
    }

    /**
     * @return Collection<int,string>
     */
    public function getHashCombination(): Collection
    {
        return $this->hashCombination;
    }

    protected function generateHashKeyName(): string
    {
        return implode('#', $this->hashCombination->toArray());
    }
}
