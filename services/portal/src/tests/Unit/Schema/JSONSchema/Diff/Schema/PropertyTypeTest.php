<?php

declare(strict_types=1);

namespace Tests\Unit\Schema\JSONSchema\Diff\Schema;

use App\Schema\Generator\JSONSchema\Diff\Schema\PropertyType;
use MinVWS\Codable\Encoder;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;

#[Group('schema-jsonschema-diff')]
class PropertyTypeTest extends UnitTestCase
{
    public static function encodeProvider(): array
    {
        return [
            'type' => [PropertyType::type('string'), 'string'],
            'ref-external' => [PropertyType::ref('/schemas/json/schemas/enums/YesNoUnknown/V1'), 'Enum-YesNoUnknown-V1'],
            'ref-internal' => [PropertyType::ref('#/$defs/Enum-YesNoUnknown-V1'), 'Enum-YesNoUnknown-V1'],
            'array-of-type' => [PropertyType::arr(PropertyType::type('string')), 'string[]'],
            'array-of-ref' => [
                PropertyType::arr(
                    PropertyType::ref('/schemas/json/schemas/enums/YesNoUnknown/V1'),
                ),
                'Enum-YesNoUnknown-V1[]',
            ],
        ];
    }

    #[DataProvider('encodeProvider')]
    public function testEncode(PropertyType $type, string $expectedEncodedType): void
    {
        $encodedType = (new Encoder())->encode($type);
        $this->assertEquals($expectedEncodedType, $encodedType);
    }
}
