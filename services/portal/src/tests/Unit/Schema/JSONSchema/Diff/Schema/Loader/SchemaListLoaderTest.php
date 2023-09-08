<?php

declare(strict_types=1);

namespace Tests\Unit\Schema\JSONSchema\Diff\Schema\Loader;

use App\Schema\Generator\JSONSchema\Diff\Schema\Loader\SchemaListLoader;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;

#[Group('schema-jsonschema-diff')]
class SchemaListLoaderTest extends UnitTestCase
{
    public static function schemaNamesProvider(): array
    {
        return [
            [[], 0],
            [['CovidCase'], 1],
            [['CovidCase', 'Event', 'Place'], 3],
            [['DoesNotExist'], 0],
        ];
    }

    #[DataProvider('schemaNamesProvider')]
    public function testLoadLocalSchemas(array $schemaNames, int $expectedSchemaCount): void
    {
        $list = SchemaListLoader::loadLocal('resources/schemas/json', $schemaNames, __DIR__ . '/../../../../../../../');
        $this->assertCount($expectedSchemaCount, $list->schemas);
    }
}
