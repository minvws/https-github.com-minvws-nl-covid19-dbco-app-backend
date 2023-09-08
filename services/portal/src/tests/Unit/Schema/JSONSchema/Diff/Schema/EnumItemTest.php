<?php

declare(strict_types=1);

namespace Tests\Unit\Schema\JSONSchema\Diff\Schema;

use App\Schema\Generator\JSONSchema\Diff\Schema\EnumItem;
use Generator;
use MinVWS\Codable\CodableException;
use MinVWS\Codable\JSONDecoder;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;
use Throwable;

use function json_encode;

#[Group('schema-jsonschema-diff')]
class EnumItemTest extends UnitTestCase
{
    public static function validProvider(): Generator
    {
        yield [
            [
                'const' => 'value',
                'description' => 'desc',
            ],
            'value',
            'desc',
        ];

        yield [
            [
                'const' => 'value',
                'description' => '',
            ],
            'value',
            '',
        ];
    }

    /**
     * @throws Throwable
     */
    #[DataProvider('validProvider')]
    public function testDecode(array $data, string $const, string $description): void
    {
        $json = json_encode($data);
        $enumItem = (new JSONDecoder())->decode($json)->decodeObject(EnumItem::class);
        $this->assertEquals($const, $enumItem->const);
        $this->assertEquals($description, $enumItem->description);
    }

    public static function invalidProvider(): Generator
    {
        yield 'missing-description' => [
            [
                'const' => 'const',
            ],
        ];

        yield 'missing-const' => [
            [
                'description' => 'description',
            ],
        ];

        yield 'invalid-const-type' => [
            [
                'const' => 1,
                'description' => 'description',
            ],
        ];

        yield 'invalid-description-type' => [
            [
                'const' => 'const',
                'description' => 1,
            ],
        ];
    }

    /**
     * @throws Throwable
     */
    #[DataProvider('invalidProvider')]
    public function testDecodeShouldThrowAnExceptionForInvalidData(array $data): void
    {
        $this->expectException(CodableException::class);
        $json = json_encode($data);
        (new JSONDecoder())->decode($json)->decodeObject(EnumItem::class);
    }
}
