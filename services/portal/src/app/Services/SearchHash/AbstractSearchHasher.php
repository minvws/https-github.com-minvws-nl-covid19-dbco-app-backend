<?php

declare(strict_types=1);

namespace App\Services\SearchHash;

use App\Services\SearchHash\Attribute\HashCombination;
use App\Services\SearchHash\Attribute\HashSource;
use App\Services\SearchHash\Dto\Contracts\GetHashCombination;
use App\Services\SearchHash\Dto\Contracts\GetHashKeyName;
use App\Services\SearchHash\Dto\ResolvedHashCombination;
use App\Services\SearchHash\Dto\SearchHashResult;
use App\Services\SearchHash\Dto\SearchHashSourceResult;
use App\Services\SearchHash\Exception\SearchHashInvalidArgumentException;
use App\Services\SearchHash\Hasher\Hasher;
use App\Services\SearchHash\Normalizer\Normalizer;
use DateTimeInterface;
use Generator;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

use function array_filter;
use function array_is_list;
use function assert;
use function count;
use function get_object_vars;
use function in_array;
use function is_array;
use function is_null;
use function is_object;
use function is_scalar;
use function is_string;
use function method_exists;
use function strlen;
use function trim;

/**
 * @template TValueObject of object
 *
 * @implements SearchHasher<TValueObject,HashCombination,HashCombination>
 */
abstract class AbstractSearchHasher implements SearchHasher
{
    /** @var Collection<int,HashCombination> */
    private Collection $hashCombinations;

    /** @var Collection<int,HashCombination> */
    private Collection $allKeys;

    /** @var Collection<int,string> */
    private Collection $hashKeysThatShouldNotExist;

    /** @var Collection<int,string> */
    private Collection $hashKeysThatShouldExist;

    /** @var Collection<int,ResolvedHashCombination> */
    private Collection $resolvedHashCombinationAttributes;

    /** @var Collection<string,?string> */
    private Collection $resolvedHashResourceAttributes;

    /**
     * @param TValueObject $valueObject
     */
    public function __construct(
        protected readonly Normalizer $hashNormalizer,
        protected readonly Hasher $hasher,
        private readonly object $valueObject,
    ) {
    }

    /**
     * @return TValueObject
     */
    public function getValueObject(): object
    {
        return $this->valueObject;
    }

    /**
     * Returns the hash combinations (the data keys) that will make up the hashes.
     *
     * @return Collection<int,HashCombination>
     */
    public function getHashCombinations(): Collection
    {
        if (isset($this->hashCombinations)) {
            return $this->hashCombinations;
        }

        return $this->hashCombinations = $this
            ->resolveHashCombinationAttributes()
            ->map(static fn (ResolvedHashCombination $hash): GetHashCombination => $hash->hashCombination);
    }

    /**
     * Generate a hash given an array of values.
     *
     * @param array<array-key,string> $values
     *
     * @phpstan-return non-empty-string
     */
    public function generateHash(array $values): string
    {
        assert(
            array_is_list($values),
            new SearchHashInvalidArgumentException('Expected param "$values" to be an array list in ->generateHash().'),
        );
        assert(
            ($c = count(array_filter($values, $this->isNotEmpty(...)))) === count($values) && $c > 0,
            new SearchHashInvalidArgumentException('Expected non-empty-string values for "$values" in ->generateHash().'),
        );

        /** @phpstan-var non-empty-string $value */
        $value = LazyCollection::make($values)
            ->pipe($this->hashNormalizer)
            ->sort()
            ->implode('#');

        return $this->hasher->hash($value);
    }

    /**
     * Returns the hash combinations (the data keys) that will make up the hashes and the hash key name.
     *
     * @return Collection<int,HashCombination>
     */
    public function getAllKeys(): Collection
    {
        if (isset($this->allKeys)) {
            return $this->allKeys;
        }

        return $this->allKeys = $this->resolveHashCombinationAttributes()
            ->map(static fn (ResolvedHashCombination $hash): GetHashCombination&GetHashKeyName => $hash->hashCombination);
    }

    /**
     * @return Collection<int,string>
     */
    public function getHashKeysThatShouldNotExist(): Collection
    {
        if (isset($this->hashKeysThatShouldNotExist)) {
            return $this->hashKeysThatShouldNotExist;
        }

        /** @var Collection<int,string> $result */
        $result = Collection::make();

        foreach ($this->getAllKeys() as $hash) {
            foreach ($hash->getHashCombination() as $valueObjectKey) {
                if (!$this->isValid($valueObjectKey) && !$result->contains($hash->getHashKeyName())) {
                    $result->push($hash->getHashKeyName());
                }
            }
        }

        return $this->hashKeysThatShouldNotExist = $result;
    }

    /**
     * @return Collection<int,string>
     */
    public function getHashKeysThatShouldExist(): Collection
    {
        if (isset($this->hashKeysThatShouldExist)) {
            return $this->hashKeysThatShouldExist;
        }

        return $this->hashKeysThatShouldExist = $this
            ->getAllKeys()
            ->map(static fn (HashCombination $hash): string => $hash->getHashKeyName())
            ->diff($this->getHashKeysThatShouldNotExist())
            ->values();
    }

    /**
     * Returns pairs of hashes and keys.
     *
     * @param array<array-key,string>|Arrayable<array-key,string> $keys
     *
     * @return LazyCollection<string,string>
     */
    public function getHashesByKeys(array|Arrayable $keys): LazyCollection
    {
        if ($keys instanceof Arrayable) {
            $keys = $keys->toArray();
        }

        $filteredResolvedHashCombinations = $this->resolveHashCombinationAttributes()
            ->filter(static fn (ResolvedHashCombination $hash): bool
                => in_array($hash->hashCombination->getHashKeyName(), $keys, true));

        return LazyCollection::make(function () use ($filteredResolvedHashCombinations): Generator {
            foreach ($filteredResolvedHashCombinations as $hash) {
                yield $hash->hashCombination->getHashKeyName() => $this->{$hash->hashMethodName}();
            }
        });
    }

    /**
     * @return LazyCollection<string,string>
     */
    public function getHashesByKeysThatShouldExist(): LazyCollection
    {
        return $this->getHashesByKeys($this->getHashKeysThatShouldExist());
    }

    /**
     * @return LazyCollection<int,SearchHashResult>
     */
    public function getAllData(array|Arrayable $keys): LazyCollection
    {
        if ($keys instanceof Arrayable) {
            $keys = $keys->toArray();
        }

        /** @var LazyCollection<string,ResolvedHashCombination> $c */
        $c = $this->resolveHashCombinationAttributes()->lazy();

        return $c
            ->filter(static fn (ResolvedHashCombination $hash): bool
                => in_array($hash->hashCombination->getHashKeyName(), $keys, true))
            ->map($this->mapIntoSearchHashResult(...))
            ->values();
    }

    public function allKeySourcesExist(): bool
    {
        return !$this->getAllKeys()
            ->contains(function (HashCombination $hash): bool {
                foreach ($hash->getHashCombination() as $valueObjectKey) {
                    if (!$this->isValid($valueObjectKey)) {
                        return true;
                    }
                }

                return false;
            }, true);
    }

    protected function mapIntoSearchHashResult(ResolvedHashCombination $hash): SearchHashResult
    {
        return new SearchHashResult(
            key: $hash->hashCombination->getHashKeyName(),
            hash: $this->{$hash->hashMethodName}(),
            sources: $hash
                ->hashCombination
                ->getHashCombination()
                ->map(fn (string $key): SearchHashSourceResult => new SearchHashSourceResult(
                    valueObjectKey: $key,
                    valueObjectValue: $this->getValueObject()->{$key},
                    sourceKey: $this->getSourceKey($key) ?? $key,
                )),
        );
    }

    /**
     * Returns the source value of the attribute that belongs to the valueobject's property named "key".
     */
    protected function getSourceKey(string $valueObjectKey): ?string
    {
        return $this->resolveHashResourceAttributes()->get($valueObjectKey);
    }

    /**
     * @return Collection<string,?string>
     */
    protected function resolveHashResourceAttributes(): Collection
    {
        if (isset($this->resolvedHashResourceAttributes)) {
            return $this->resolvedHashResourceAttributes;
        }

        $result = Collection::make((new ReflectionClass($this->getValueObject()::class))->getProperties(ReflectionProperty::IS_PUBLIC))
            ->mapWithKeys(static function (ReflectionProperty $property): array {
                /** @var ?ReflectionAttribute $attribute */
                $attribute = Arr::first($property->getAttributes(HashSource::class));

                /** @var ?HashSource $hashSource */
                $hashSource = $attribute?->newInstance();

                return [$property->getName() => $hashSource?->source];
            });

        return $this->resolvedHashResourceAttributes = $result;
    }

    /**
     * @return Collection<int,ResolvedHashCombination>
     */
    protected function resolveHashCombinationAttributes(): Collection
    {
        if (isset($this->resolvedHashCombinationAttributes)) {
            return $this->resolvedHashCombinationAttributes;
        }

        /** @var Collection<ResolvedHashCombination> $result*/
        $result = Collection::make();

        foreach ((new ReflectionClass(static::class))->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            foreach ($method->getAttributes(HashCombination::class) as $attribute) {
                /** @var HashCombination $hashCombination */
                $hashCombination = $attribute->newInstance();

                $result->add(
                    new ResolvedHashCombination(
                        hashMethodName: $method->getName(),
                        hashCombination: $hashCombination,
                    ),
                );
            }
        }

        return $this->resolvedHashCombinationAttributes = $result;
    }

    private function isValid(string $key, mixed $data = null): bool
    {
        $data ??= $this->getValueObject();

        if (is_object($data) && method_exists($data, 'isOptional') && $data->isOptional($key)) {
            return true;
        }

        if (!is_object($data)) {
            assert(is_scalar($data) || is_null($data));

            return $this->isNotEmpty($data);
        }

        $value = $data->{$key};

        if ($value instanceof DateTimeInterface) {
            return true;
        }

        if ($value instanceof IsValid) {
            return $value->isValid();
        }

        if (is_object($value)) {
            foreach (get_object_vars($value) as $subKey => $subData) {
                if (!$this->isValid($subKey, $subData)) {
                    return false;
                }
            }
        }

        return $this->isNotEmpty($value);
    }

    /**
     * Only null, empty strings, or strings only containing whitespace are considered "empty".
     */
    private function isNotEmpty(mixed $value): bool
    {
        if (is_null($value)) {
            return false;
        }

        if (is_object($value) || is_array($value)) {
            return true;
        }

        if (is_string($value) && strlen(trim($value)) > 0) {
            return true;
        }

        return !empty($value);
    }
}
