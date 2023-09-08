<?php

declare(strict_types=1);

namespace Tests\Unit\Services\SearchHash\Hasher;

use App\Services\SearchHash\Hasher\Hasher;
use App\Services\SearchHash\Hasher\Pbkdf2Hasher;
use PHPUnit\Framework\Attributes\Group;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\UnitTestCase;

#[Group('search-hash')]
final class Pbkdf2HasherTest extends UnitTestCase
{
    use MatchesSnapshots;

    public function testItCanBeInitialized(): void
    {
        $hasher = new Pbkdf2Hasher(salt: 'my_test_index_salt', hashIterations: 1, hashAlgo: 'sha3-512');

        $this->assertInstanceOf(Pbkdf2Hasher::class, $hasher);
        $this->assertInstanceOf(Hasher::class, $hasher);
    }

    public function testHash(): void
    {
        $hasher = new Pbkdf2Hasher(salt: 'my_test_index_salt', hashIterations: 1, hashAlgo: 'sha3-512');

        $value = 'my_to_be_hashed_value';

        $result = $hasher->hash($value);
        $result2 = $hasher->hash($value);

        $this->assertMatchesTextSnapshot($result);
        $this->assertSame($result, $result2, 'Hashing the same value twice did not produce the same hash!');
    }
}
