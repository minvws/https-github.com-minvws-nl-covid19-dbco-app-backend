<?php

declare(strict_types=1);

namespace App\Services\SearchHash;

use App\Services\SearchHash\Dto\Contracts\GetHashCombination;
use App\Services\SearchHash\Dto\Contracts\GetHashKeyName;
use App\Services\SearchHash\Dto\SearchHashResult;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;

/**
 * @template TValueObject of object
 * @template THashValueCombination of GetHashCombination
 * @template THashCombinationHashKeyName of GetHashCombination&GetHashKeyName
 */
interface SearchHasher
{
    /**
     * @return TValueObject
     */
    public function getValueObject(): object;

    /**
     * Returns the hash combinations (the data keys) that will make up the hashes.
     *
     * @return Collection<int,THashValueCombination>
     */
    public function getHashCombinations(): Collection;

    /**
     * Returns the hash combinations (the data keys) that will make up the hashes and the hash key name.
     *
     * @return Collection<int,THashCombinationHashKeyName>
     */
    public function getAllKeys(): Collection;

    /**
     * @return Collection<int,string>
     */
    public function getHashKeysThatShouldNotExist(): Collection;

    /**
     * @return Collection<int,string>
     */
    public function getHashKeysThatShouldExist(): Collection;

    /**
     * Returns pairs of hashes and keys.
     *
     * @param array<array-key,string>|Arrayable<array-key,string> $keys
     *
     * @return LazyCollection<string,string>
     */
    public function getHashesByKeys(array|Arrayable $keys): LazyCollection;

    /**
     * @return LazyCollection<int,SearchHashResult>
     */
    public function getAllData(array|Arrayable $keys): LazyCollection;

    public function allKeySourcesExist(): bool;
}
