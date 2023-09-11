<?php

declare(strict_types=1);

namespace Tests\Unit\Schema;

use App\Schema\Schema;
use App\Schema\SchemaCache;
use stdClass;
use Tests\Unit\UnitTestCase;

class SchemaCacheTest extends UnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        SchemaCache::clear();
    }

    protected function tearDown(): void
    {
        SchemaCache::clear();

        parent::tearDown();
    }

    public function testClear(): void
    {
        $count = 0;
        $load = static function () use (&$count) {
            $count++;
            return new Schema(stdClass::class);
        };

        $schema1 = SchemaCache::get(stdClass::class, $load);
        $this->assertEquals(1, $count);

        $schema2 = SchemaCache::get(stdClass::class, $load);
        $this->assertEquals(1, $count);
        $this->assertTrue($schema2 === $schema1);

        SchemaCache::clear();

        $schema3 = SchemaCache::get(stdClass::class, $load);
        $this->assertEquals(2, $count);
        $this->assertFalse($schema3 === $schema1);
    }
}
