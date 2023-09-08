<?php

declare(strict_types=1);

namespace Tests\Unit\Services\SearchHash\Attribute;

use App\Services\SearchHash\Attribute\HashCombination;
use App\Services\SearchHash\Dto\Contracts\GetHashCombination;
use App\Services\SearchHash\Dto\Contracts\GetHashKeyName;
use App\Services\SearchHash\Exception\SearchHashInvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;

#[Group('search-hash')]
final class HashCombinationTest extends UnitTestCase
{
    public function testItCanBeInitialized(): void
    {
        $this->assertInstanceOf(HashCombination::class, new HashCombination('key1'));
    }

    #[DataProvider('itThrowsAnExceptionIfGivenInvalidKeysData')]
    public function testItThrowsAnExceptionIfGivenInvalidKeys(string $expectedExceptionMessage, array $keys): void
    {
        $this->expectExceptionObject(new SearchHashInvalidArgumentException($expectedExceptionMessage));

        new HashCombination(...$keys);
    }

    public function testGetHashCombination(): void
    {
        $keys = [
            'key1   ',
            ' key2',
            'key3',
        ];

        $expected = [
            'key1',
            'key2',
            'key3',
        ];

        $hash = new HashCombination(...$keys);

        $this->assertInstanceOf(GetHashCombination::class, $hash);
        $this->assertSame($expected, $hash->getHashCombination()->toArray());
    }

    #[DataProvider('getKeyNameData')]
    public function testGetKeyName(array $keys, string $expected): void
    {
        $hash = new HashCombination(...$keys);

        $this->assertInstanceOf(GetHashKeyName::class, $hash);
        $this->assertSame($expected, $hash->getHashKeyName());
    }

    public static function itThrowsAnExceptionIfGivenInvalidKeysData(): array
    {
        return [
            'empty array' => [
                'expectedExceptionMessage' => 'Expected atleast 1 key.',
                'keys' => [],
            ],
            'single empty value' => [
                'expectedExceptionMessage' => 'Expected non-empty-string key values.',
                'keys' => [''],
            ],
            'multiple empty values' => [
                'expectedExceptionMessage' => 'Expected non-empty-string key values.',
                'keys' => ['', ''],
            ],
            'multple empty values with spaces' => [
                'expectedExceptionMessage' => 'Expected non-empty-string key values.',
                'keys' => ['  ', ''],
            ],
            'mixed empty and non-empty values' => [
                'expectedExceptionMessage' => 'Expected non-empty-string key values.',
                'keys' => ['my-key', ''],
            ],
            'non-empty values with one only consisting of spaces' => [
                'expectedExceptionMessage' => 'Expected non-empty-string key values.',
                'keys' => ['my-key', '  '],
            ],
        ];
    }

    public static function getKeyNameData(): array
    {
        return [
            'single key' => [
                'keys' => [
                    'key1 ',
                ],
                'expected' => 'key1',
            ],
            'multiple keys' => [
                'keys' => [
                    'key1',
                    'key2',
                ],
                'expected' => 'key1#key2',
            ],
            'keys are sorted' => [
                'keys' => [
                    'key2',
                    'key1',
                ],
                'expected' => 'key1#key2',
            ],
            'keys with spaces are trimmed and are sorted' => [
                'keys' => [
                    '   key2 ',
                    'key1 ',
                ],
                'expected' => 'key1#key2',
            ],
            'keys are sorted naturally and case-insensitive' => [
                'keys' => [
                    'key8',
                    'key10',
                    'KEY2',
                    'KEY11',
                    'key1',
                    'KEY1',
                ],
                'expected' => 'key1#KEY1#KEY2#key8#key10#KEY11',
            ],
        ];
    }
}
