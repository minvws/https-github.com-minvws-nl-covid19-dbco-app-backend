<?php

declare(strict_types=1);

namespace Tests\Unit\Schema\JSONSchema\Diff\Schema;

use App\Schema\Generator\JSONSchema\Diff\Schema\Property;
use Generator;
use MinVWS\Codable\JSONDecoder;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;
use Throwable;

use function json_encode;

#[Group('schema-jsonschema-diff')]
class PropertyTest extends UnitTestCase
{
    public static function validProvider(): Generator
    {
        yield [
            [
                'prop' => [],
            ],
            'prop',
            'any',
            null,
            null,
            null,
        ];

        yield [
            [
                'name' => [
                    'type' => 'string',
                ],
            ],
            'name',
            'string',
            null,
            null,
            null,
        ];

        yield [
            [
                'name' => [
                    '$ref' => '/a/b/c',
                ],
            ],
            'name',
            null,
            '/a/b/c',
            null,
            null,
        ];

        yield [
            [
                'name' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'string',
                    ],
                ],
            ],
            'name',
            'array',
            null,
            'string',
            null,
        ];

        yield [
            [
                'name' => [
                    'type' => 'array',
                    'items' => [
                        '$ref' => '/a/b/c',
                    ],
                ],
            ],
            'name',
            'array',
            null,
            null,
            '/a/b/c',
        ];
    }

    /**
     * @throws Throwable
     */
    #[DataProvider('validProvider')]
    public function testDecode(array $data, string $name, ?string $type, ?string $ref, ?string $itemType, ?string $itemRef): void
    {
        $json = json_encode($data);
        $properties = (new JSONDecoder())->decode($json)->decodeArray(Property::class);
        $this->assertCount(1, $properties);
        $this->assertArrayHasKey($name, $properties);
        $property = $properties[$name];
        $this->assertInstanceOf(Property::class, $property);
        $this->assertEquals($type, $property->type->type);
        $this->assertEquals($ref, $property->type->ref);
        $this->assertEquals($itemType, $property->type->itemType?->type);
        $this->assertEquals($itemRef, $property->type->itemType?->ref);
    }
}
