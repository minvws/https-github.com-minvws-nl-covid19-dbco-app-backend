<?php

declare(strict_types=1);

namespace Tests\Unit\Schema\JSONSchema\Diff\Schema;

use App\Schema\Generator\JSONSchema\Diff\Schema\EnumVersion;
use Generator;
use MinVWS\Codable\CodableException;
use MinVWS\Codable\JSONDecoder;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;
use Throwable;

use function json_encode;

#[Group('schema-jsonschema-diff')]
class EnumVersionTest extends UnitTestCase
{
    public static function validProvider(): Generator
    {
        yield [
            [
                '$id' => '/schemas/json/schemas/enums/YesNoUnknown/V1',
                'oneOf' => [
                    ['const' => 'yes', 'description' => 'Ja'],
                    ['const' => 'no', 'description' => 'Nee'],
                    ['const' => 'unknown', 'description' => 'Onbekend'],
                ],
            ],
            'Enum-YesNoUnknown',
            1,
            3,
        ];

        yield [
            [
                '$id' => '/schemas/json/schemas/enums/YesNoUnknown/V2',
                'oneOf' => [
                    ['const' => 'yes', 'description' => 'Ja'],
                    ['const' => 'no', 'description' => 'Nee'],
                ],
            ],
            'Enum-YesNoUnknown',
            2,
            2,
        ];
    }

    /**
     * @throws Throwable
     */
    #[DataProvider('validProvider')]
    public function testDecode(array $data, string $name, int $version, int $itemCount): void
    {
        $json = json_encode($data);
        $enumVersion = (new JSONDecoder())->decode($json)->decodeObject(EnumVersion::class);
        $this->assertEquals($name, $enumVersion->name);
        $this->assertEquals($version, $enumVersion->version);
        $this->assertCount($itemCount, $enumVersion->items);
    }

    public static function invalidProvider(): Generator
    {
        yield 'missing-$id' => [
            [
                'oneOf' => [
                    ['const' => 'yes', 'description' => 'Ja'],
                    ['const' => 'no', 'description' => 'Nee'],
                    ['const' => 'unknown', 'description' => 'Onbekend'],
                ],
            ],
        ];

        yield 'missing-oneOf' => [
            [
                '$id' => '/schemas/json/schemas/enums/YesNoUnknown/V1',
            ],
        ];

        yield 'invalid-$id-type' => [
            [
                '$id' => 1,
                'oneOf' => [
                    ['const' => 'yes', 'description' => 'Ja'],
                    ['const' => 'no', 'description' => 'Nee'],
                    ['const' => 'unknown', 'description' => 'Onbekend'],
                ],
            ],
        ];

        yield 'invalid-oneOf-type' => [
            [
                '$id' => '/schemas/json/schemas/enums/YesNoUnknown/V1',
                'oneOf' => 'oneOf',
            ],
        ];
    }

    /**
     * @throws Throwable
     */
    #[DataProvider('invalidProvider')]
    public function testDecodeShouldThrowException(array $data): void
    {
        $this->expectException(CodableException::class);
        $json = json_encode($data);
        (new JSONDecoder())->decode($json)->decodeObject(EnumVersion::class);
    }
}
