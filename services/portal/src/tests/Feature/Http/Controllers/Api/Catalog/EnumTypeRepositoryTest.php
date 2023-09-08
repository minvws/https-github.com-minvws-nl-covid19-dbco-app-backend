<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api\Catalog;

use App\Services\Catalog\EnumTypeRepository;
use Tests\TestCase;

use function count;
use function glob;

class EnumTypeRepositoryTest extends TestCase
{
    public function getEnumCount(): ?int
    {
        return count(glob(__DIR__ . '/../Dummy/Enums/*.json')) - 2;
    }

    public function testItCanReturnAllEnumTypes(): void
    {
        $repository = new EnumTypeRepository(__DIR__ . '/../Dummy/Enums/index.json', 'Tests\\Feature\\Http\\Controllers\\Api\\Dummy\\');

        //Remove 2 from the count because we have 2 dummy indexes that are not counted
        $count = $this->getEnumCount();
        self::assertCount($count, $repository->getEnumTypes(), 'Expected to find ' . $count . ' enum types');
    }

    public function testItDoesNotReturnAnyEnumsWhenIndexJsonEmpty(): void
    {
        $repository = new EnumTypeRepository(
            __DIR__ . '/../Dummy/Enums/empty.index.json',
            'Tests\\Feature\\Http\\Controllers\\Api\\Dummy\\',
        );
        $count = $this->getEnumCount();
        self::assertNotCount($count, $repository->getEnumTypes());
    }
}
