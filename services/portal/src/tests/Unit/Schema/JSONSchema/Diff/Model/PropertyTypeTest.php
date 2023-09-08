<?php

declare(strict_types=1);

namespace Tests\Unit\Schema\JSONSchema\Diff\Model;

use App\Schema\Generator\JSONSchema\Diff\Model\DiffType;
use App\Schema\Generator\JSONSchema\Diff\Schema\PropertyType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;

#[Group('schema-jsonschema-diff')]
class PropertyTypeTest extends UnitTestCase
{
    public static function unmodifiedProvider(): array
    {
        return [
            'equal-type' => [
                PropertyType::type('string'),
                PropertyType::type('string'),
            ],
            'equal-ref' => [
                PropertyType::ref('/schemas/json/schemas/enums/YesNoUnknown/V1'),
                PropertyType::ref('/schemas/json/schemas/enums/YesNoUnknown/V1'),
            ],
            'equal-array-type' => [
                PropertyType::arr(PropertyType::type('int')),
                PropertyType::arr(PropertyType::type('int')),
            ],
            'equal-array-ref' => [
                PropertyType::arr(PropertyType::ref('/schemas/json/schemas/enums/YesNoUnknown/V1')),
                PropertyType::arr(PropertyType::ref('/schemas/json/schemas/enums/YesNoUnknown/V1')),
            ],
        ];
    }

    #[DataProvider('unmodifiedProvider')]
    public function testDiffShouldReturnNullIfUnmodified(PropertyType $original, PropertyType $new): void
    {
        $this->assertNull($new->diff($original));
        $this->assertNull($original->diff($new));
    }

    public static function modifiedProvider(): array
    {
        return [
            'type-modified' => [
                PropertyType::type('string'),
                PropertyType::type('int'),
            ],
            'ref-version-modified' => [
                PropertyType::ref('/schemas/json/schemas/enums/YesNoUnknown/V1'),
                PropertyType::ref('/schemas/json/schemas/enums/YesNoUnknown/V2'),
            ],
            'ref-to-type' => [
                PropertyType::ref('/schemas/json/schemas/enums/YesNoUnknown/V1'),
                PropertyType::type('string'),
            ],
            'type-to-ref' => [
                PropertyType::type('string'),
                PropertyType::ref('/schemas/json/schemas/enums/YesNoUnknown/V1'),
            ],
            'array-item-type-modified' => [
                PropertyType::arr(PropertyType::type('int')),
                PropertyType::arr(PropertyType::type('string')),
            ],
            'array-ref-version-modified' => [
                PropertyType::arr(PropertyType::ref('/schemas/json/schemas/enums/YesNoUnknown/V1')),
                PropertyType::arr(PropertyType::ref('/schemas/json/schemas/enums/YesNoUnknown/V2')),
            ],
            'array-ref-to-type' => [
                PropertyType::arr(PropertyType::ref('/schemas/json/schemas/enums/YesNoUnknown/V1')),
                PropertyType::arr(PropertyType::type('string')),
            ],
            'array-type-to-ref' => [
                PropertyType::arr(PropertyType::type('string')),
                PropertyType::arr(PropertyType::ref('/schemas/json/schemas/enums/YesNoUnknown/V1')),
            ],
        ];
    }

    #[DataProvider('modifiedProvider')]
    public function testDiffShouldReturnModified(PropertyType $original, PropertyType $new): void
    {
        $diff = $new->diff($original);
        $this->assertNotNull($diff);
        $this->assertEquals(DiffType::Modified, $diff->diffType);
        $this->assertSame($new, $diff->new);
        $this->assertSame($original, $diff->original);
    }
}
