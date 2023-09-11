<?php

declare(strict_types=1);

namespace Tests\Unit\Services\SearchHash;

use App\Services\SearchHash\AbstractSearchHasher;
use App\Services\SearchHash\Attribute\HashCombination;
use App\Services\SearchHash\Attribute\HashSource;
use App\Services\SearchHash\Dto\Contracts\GetHashCombination;
use App\Services\SearchHash\Dto\Contracts\GetHashKeyName;
use App\Services\SearchHash\Dto\SearchHashResult;
use App\Services\SearchHash\Dto\SearchHashSourceResult;
use App\Services\SearchHash\Exception\SearchHashInvalidArgumentException;
use App\Services\SearchHash\Hasher\NoOpHasher;
use App\Services\SearchHash\IsValid;
use App\Services\SearchHash\Normalizer\NoOpNormalizer;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestDox;
use stdClass;
use Tests\Unit\UnitTestCase;

use function array_is_list;

#[Group('search-hash')]
final class AbstractSearchHasherTest extends UnitTestCase
{
    public function testGenerateHash(): void
    {
        $fixtureValues1 = ['my_username', '1950-01-01'];

        $hash = $this->getHasherFixtureWithoutHashMethods()->generateHash($fixtureValues1);

        $this->assertSame('1950-01-01#my_username', $hash, 'The hash value is not composed the same as expected');
    }

    #[DataProvider('generateHashThrowsAnExceptionData')]
    #[TestDox('generateHash throws an exception when given values $_dataName')]
    public function testGenerateHashThrowsAnException(string $expectedExceptionMessage, array $values): void
    {
        $this->expectExceptionObject(new SearchHashInvalidArgumentException($expectedExceptionMessage));

        $this->getHasherFixtureWithoutHashMethods()->generateHash($values);
    }

    #[DataProvider('getAllKeysThrowsAnExceptionData')]
    public function testGetAllKeysThrowsAnException(AbstractSearchHasher $hasher, string $expectedExceptionMessage): void
    {
        $this->expectExceptionObject(new SearchHashInvalidArgumentException($expectedExceptionMessage));

        $hasher->getAllKeys();
    }

    public function testGetAllKeys(): void
    {
        $hasher = $this->getHasherFixture();
        $result = $hasher->getAllKeys();

        $expected = [
            'dateOfBirth#lastname' => ['dateOfBirth', 'lastname'],
            'dateOfBirth#firstname' => ['dateOfBirth', 'firstname'],
            'dateOfBirth#firstname#lastname' => ['dateOfBirth', 'firstname', 'lastname'],
            'address#dateOfBirth' => ['address', 'dateOfBirth'],
        ];

        foreach ($result as $hashCombination) {
            $this->assertInstanceOf(HashCombination::class, $hashCombination);
        }

        $this->assertEquals(
            $expected,
            $result
                ->mapWithKeys(static fn (GetHashCombination&GetHashKeyName $hash): array
                    => [$hash->getHashKeyName() => $hash->getHashCombination()])
                ->toArray(),
        );
    }

    #[DataProvider('getHashKeysThatShouldAndShouldNotExistData')]
    public function testGetHashKeysThatShouldAndShouldNotExist(
        array $valueObjectData,
        array $expectedShouldExist,
        array $expectedShouldNotExist,
    ): void {
        $hasher = $this->getHasherFixture((object) $valueObjectData);

        $resultShouldExist = $hasher->getHashKeysThatShouldExist();
        $resultShouldNotExist = $hasher->getHashKeysThatShouldNotExist();

        $this->assertSame(
            $expectedShouldExist,
            $resultShouldExist->toArray(),
            '->getHashKeysThatShouldExist() method result does not match expected value',
        );
        $this->assertSame(
            $resultShouldExist,
            $hasher->getHashKeysThatShouldExist(),
            'Expected ->getHashKeysThatShouldExist() to return the same (cached) Collection',
        );

        $this->assertSame(
            $expectedShouldNotExist,
            $resultShouldNotExist->toArray(),
            '->getHashKeysThatShouldNotExist() method result does not match expected value',
        );
        $this->assertSame(
            $resultShouldNotExist,
            $hasher->getHashKeysThatShouldNotExist(),
            'Expected ->getHashKeysThatShouldNotExist() to return the same (cached) Collection',
        );
    }

    public function testGetHashCombinations(): void
    {
        $hasher = $this->getHasherFixture();
        $result = $hasher->getHashCombinations();

        $expectedHashCombinations = [
            ['dateOfBirth', 'lastname'],
            ['dateOfBirth', 'firstname'],
            ['dateOfBirth', 'firstname', 'lastname'],
            ['address', 'dateOfBirth'],
        ];

        foreach ($result as $hashCombination) {
            $this->assertInstanceOf(GetHashCombination::class, $hashCombination);
        }

        $this->assertEquals(
            $expectedHashCombinations,
            $result->map(static fn (GetHashCombination $hash): Collection => $hash->getHashCombination())->toArray(),
        );
        $this->assertSame($result, $hasher->getHashCombinations());
    }

    /**
     * resolveHashCombinationAttributes() is an protected method and so we only want to test that its internally cached
     * correctly.
     */
    public function testResolveHashCombinationAttributesIsCached(): void
    {
        $hasher = new class (new NoOpNormalizer(), new NoOpHasher(), new stdClass()) extends AbstractSearchHasher {
            /**
             * @return Collection<string, HashCombination>
            */
            public function resolve(): Collection
            {
                return $this->resolveHashCombinationAttributes();
            }
        };

        $this->assertSame($hasher->resolve(), $hasher->resolve());
    }

    /**
     * testResolveHashResourceAttributesIsCached() is an protected method and so we only want to test that its internally cached
     * correctly.
     */
    public function testResolveHashResourceAttributesIsCached(): void
    {
        $hasher = new class (new NoOpNormalizer(), new NoOpHasher(), new stdClass()) extends AbstractSearchHasher {
            /**
             * @return Collection<string, HashCombination>
            */
            public function resolve(): Collection
            {
                return $this->resolveHashResourceAttributes();
            }
        };

        $this->assertSame($hasher->resolve(), $hasher->resolve());
    }

    public function testGetHashesByKeys(): void
    {
        $valueObject = (object) [
            'dateOfBirth' => $this->faker->dateTimeBetween(),
            'firstname' => $this->faker->firstName(),
            'lastname' => $this->faker->lastName(),
            'address' => null,
        ];

        $hasher = $this->getHasherFixture($valueObject);

        /** @var Collection<int, string> $keys */
        $keys = Collection::make(['dateOfBirth#lastname', 'dateOfBirth#firstname', 'dateOfBirth#firstname#lastname']);

        $expected = [
            'dateOfBirth#lastname' => 'lastname_hash',
            'dateOfBirth#firstname' => 'firstname_hash',
            'dateOfBirth#firstname#lastname' => 'bio_hash',
        ];

        $this->assertSame($expected, $hasher->getHashesByKeys($keys)->toArray());
    }

    public function testGetHashesByKeysThatShouldExist(): void
    {
        $valueObject = (object) [
            'dateOfBirth' => $this->faker->dateTimeBetween(),
            'firstname' => $this->faker->firstName(),
            'lastname' => null,
            'address' => null,
        ];

        $hasher = $this->getHasherFixture($valueObject);

        $expected = ['dateOfBirth#firstname' => 'firstname_hash'];

        $this->assertSame($expected, $hasher->getHashesByKeysThatShouldExist()->toArray());
    }

    public function testGetAllData(): void
    {
        $valueObject = new class (
            dateOfBirth: CarbonImmutable::createFromFormat('Y-m-d', '2005-08-20'),
            firstname: 'Evy',
            lastname: 'van Gelder',
            address: 'van den Eerenbeemtsteeg 6530',
        ) {
            public function __construct(
                #[HashSource('index.dateOfBirth')]
                public readonly DateTimeInterface $dateOfBirth,
                // Without HashSource so it should default to the param name for it's source
                public readonly string $firstname,
                #[HashSource('index.lastname')]
                public readonly string $lastname,
                #[HashSource('index.address')]
                public readonly string $address,
            ) {
            }
        };

        $keys = Collection::make(['dateOfBirth#lastname', 'dateOfBirth#firstname']);

        $result = $this->getHasherFixture($valueObject)->getAllData($keys);

        $this->assertSame($keys->count(), $result->count());

        $this->assertEquals($result->toArray(), [
            new SearchHashResult(
                key: 'dateOfBirth#lastname',
                hash: 'lastname_hash',
                sources: Collection::make([
                    new SearchHashSourceResult(
                        valueObjectKey: 'dateOfBirth',
                        valueObjectValue: $valueObject->dateOfBirth,
                        sourceKey: 'index.dateOfBirth',
                    ),
                    new SearchHashSourceResult(
                        valueObjectKey: 'lastname',
                        valueObjectValue: $valueObject->lastname,
                        sourceKey: 'index.lastname',
                    ),
                ]),
            ),
            new SearchHashResult(
                key: 'dateOfBirth#firstname',
                hash: 'firstname_hash',
                sources: Collection::make([
                    new SearchHashSourceResult(
                        valueObjectKey: 'dateOfBirth',
                        valueObjectValue: $valueObject->dateOfBirth,
                        sourceKey: 'index.dateOfBirth',
                    ),
                    new SearchHashSourceResult(
                        valueObjectKey: 'firstname',
                        valueObjectValue: $valueObject->firstname,
                        sourceKey: 'firstname',
                    ),
                ]),
            ),
        ]);
    }

    public function testAllKeySourcesExist(): void
    {
        $valueObject = new class (
            dateOfBirth: CarbonImmutable::createFromFormat('Y-m-d', '2005-08-20'),
            firstname: 'Evy',
            lastname: null,
            address: 'van den Eerenbeemtsteeg 6530',
        ) {
            public function __construct(
                #[HashSource('index.dateOfBirth')]
                public readonly DateTimeInterface $dateOfBirth,
                // Without HashSource so it should default to the param name for it's source
                public readonly string $firstname,
                #[HashSource('index.lastname')]
                public readonly ?string $lastname,
                #[HashSource('index.address')]
                public readonly string $address,
            ) {
            }

            public function isOptional(string $key): bool
            {
                return $key === 'lastname';
            }
        };

        $valueObject2 = new class (
            dateOfBirth: CarbonImmutable::createFromFormat('Y-m-d', '2005-08-20'),
            firstname: 'Evy',
            lastname: null,
            address: 'van den Eerenbeemtsteeg 6530',
        ) {
            public function __construct(
                #[HashSource('index.dateOfBirth')]
                public readonly DateTimeInterface $dateOfBirth,
                // Without HashSource so it should default to the param name for it's source
                public readonly string $firstname,
                #[HashSource('index.lastname')]
                public readonly ?string $lastname,
                #[HashSource('index.address')]
                public readonly string $address,
            ) {
            }
        };

        $result1 = $this->getHasherFixture($valueObject)->allKeySourcesExist();
        $result2 = $this->getHasherFixture($valueObject2)->allKeySourcesExist();

        $this->assertTrue($result1);
        $this->assertFalse($result2);
    }

    public function testAllMethodsReturnAnEmptyArrayIfNoHashMethodsAreSet(): void
    {
        $hasher = $this->getHasherFixtureWithoutHashMethods();

        $this->assertSame([], $hasher->getAllKeys()->toArray());
        $this->assertSame([], $hasher->getHashKeysThatShouldExist()->toArray());
        $this->assertSame([], $hasher->getHashKeysThatShouldNotExist()->toArray());
        $this->assertSame([], $hasher->getHashCombinations()->toArray());
    }

    public function testGetHashKeysMethodsReturnsAList(): void
    {
        $valueObject = (object) [
            'firstname' => $this->faker->firstName(),
            'lastname' => null,
            'address' => $this->faker->streetAddress(),
            'dateOfBirth' => $this->faker->dateTimeBetween(),
        ];

        $hasher = $this->getHasherFixture($valueObject);

        $keysThatShouldExist = $hasher->getHashKeysThatShouldExist();
        $keysThatShouldNotExist = $hasher->getHashKeysThatShouldNotExist();

        $this->assertTrue(array_is_list($keysThatShouldExist->toArray()), 'getHashKeysThatShouldExist does not return a list');
        $this->assertTrue(array_is_list($keysThatShouldNotExist->toArray()), 'getHashKeysThatShouldNotExist does not return a list');
    }

    public static function getHashKeysThatShouldAndShouldNotExistData(): array
    {
        return [
            'all data containing a valid value' => [
                'valueObjectData' => [
                    'dateOfBirth' => '01-01-1950',
                    'firstname' => 'foo',
                    'lastname' => 'bar',
                    'address' => 'Foobar 1',
                ],
                'expectedShouldExist' => [
                    'dateOfBirth#lastname',
                    'dateOfBirth#firstname',
                    'dateOfBirth#firstname#lastname',
                    'address#dateOfBirth',
                ],
                'expectedShouldNotExist' => [],
            ],
            'with a single value given a null' => [
                'valueObjectData' => [
                    'dateOfBirth' => '01-01-1950',
                    'firstname' => 'foo',
                    'lastname' => 'bar',
                    'address' => null,
                ],
                'expectedShouldExist' => [
                    'dateOfBirth#lastname',
                    'dateOfBirth#firstname',
                    'dateOfBirth#firstname#lastname',
                ],
                'expectedShouldNotExist' => [
                    'address#dateOfBirth',
                ],
            ],
            'with a single value given an empty string' => [
                'valueObjectData' => [
                    'dateOfBirth' => '01-01-1950',
                    'firstname' => null,
                    'lastname' => 'bar',
                    'address' => 'Foobar 1',
                ],
                'expectedShouldExist' => [
                    'dateOfBirth#lastname',
                    'address#dateOfBirth',
                ],
                'expectedShouldNotExist' => [
                    'dateOfBirth#firstname',
                    'dateOfBirth#firstname#lastname',
                ],
            ],
            'using nested stdClass objects in ValueObject' => [
                'valueObjectData' => [
                    'dateOfBirth' => '01-01-1950',
                    'lastname' => (object) [
                        'subValue1' => '',
                        'subValue2' => 'bar',
                    ],
                    'firstname' => null,
                    'address' => (object) [
                        'subValue1' => 'foo',
                        'subValue2' => 'bar',
                    ],
                ],
                'expectedShouldExist' => [
                    'address#dateOfBirth',
                ],
                'expectedShouldNotExist' => [
                    'dateOfBirth#lastname',
                    'dateOfBirth#firstname',
                    'dateOfBirth#firstname#lastname',
                ],
            ],
            'using nested objects that implement IsValid' => [
                'valueObjectData' => [
                    'dateOfBirth' => '01-01-1950',
                    'address' => new class () implements IsValid {
                        public function isValid(): bool
                        {
                            return true;
                        }
                    },
                    'firstname' => 'foo',
                    'lastname' => new class () implements IsValid {
                        public function isValid(): bool
                        {
                            return false;
                        }
                    },
                ],
                'expectedShouldExist' => [
                    'dateOfBirth#firstname',
                    'address#dateOfBirth',
                ],
                'expectedShouldNotExist' => [
                    'dateOfBirth#lastname',
                    'dateOfBirth#firstname#lastname',
                ],
            ],
        ];
    }

    public static function generateHashThrowsAnExceptionData(): array
    {
        return [
            'is not a list' => [
                'expectedExceptionMessage' => 'Expected param "$values" to be an array list in ->generateHash().',
                'values' => [
                    'one' => 'foo',
                    'two' => 'bar',
                ],
            ],
            'contains an empty string value' => [
                'expectedExceptionMessage' => 'Expected non-empty-string values for "$values" in ->generateHash()',
                'values' => [
                    'foo',
                    '',
                ],
            ],
        ];
    }

    public static function getAllKeysThrowsAnExceptionData(): array
    {
        return [
            'an HashCombination attribute that was given no keys' => [
                'hasher' => new class (new NoOpNormalizer(), new NoOpHasher(), new stdClass()) extends AbstractSearchHasher {
                    #[HashCombination()]
                    public function getLastnameHash(): void
                    {
                    }
                },
                'expectedExceptionMessage' => 'Expected atleast 1 key.',
            ],
            'an HashCombination attribute that was given an empty string as key' => [
                'hasher' => new class (new NoOpNormalizer(), new NoOpHasher(), new stdClass()) extends AbstractSearchHasher {
                    #[HashCombination('')]
                    public function getLastnameHash(): void
                    {
                    }
                },
                'expectedExceptionMessage' => 'Expected non-empty-string key values.',
            ],
        ];
    }

    protected function getHasherFixture(object $valueObject = new stdClass()): AbstractSearchHasher
    {
        return new class (new NoOpNormalizer(), new NoOpHasher(), $valueObject) extends AbstractSearchHasher {
            #[HashCombination('lastname', 'dateOfBirth')]
            public function getLastnameHash(): string
            {
                return 'lastname_hash';
            }

            #[HashCombination('firstname', 'dateOfBirth')]
            public function getFirstnameHash(): string
            {
                return 'firstname_hash';
            }

            #[HashCombination('firstname', 'lastname', 'dateOfBirth')]
            public function getBioHash(): string
            {
                return 'bio_hash';
            }

            #[HashCombination('address', 'dateOfBirth')]
            public function getAddressHash(): string
            {
                return 'address_hash';
            }
        };
    }

    protected function getHasherFixtureWithoutHashMethods(object $valueObject = new stdClass()): AbstractSearchHasher
    {
        return new class (new NoOpNormalizer(), new NoOpHasher(), $valueObject) extends AbstractSearchHasher {
        };
    }
}
