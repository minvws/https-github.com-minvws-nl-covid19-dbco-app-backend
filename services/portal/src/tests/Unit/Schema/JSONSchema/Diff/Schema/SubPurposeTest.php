<?php

declare(strict_types=1);

namespace Tests\Unit\Schema\JSONSchema\Diff\Schema;

use App\Schema\Generator\JSONSchema\Diff\Schema\SubPurpose;
use Generator;
use MinVWS\Codable\CodableException;
use MinVWS\Codable\JSONDecoder;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;
use Throwable;

use function json_encode;

#[Group('schema-jsonschema-diff')]
class SubPurposeTest extends UnitTestCase
{
    public static function validProvider(): Generator
    {
        yield [
            [
                'identifier' => 'id',
                'description' => 'desc',
            ],
            'id',
            'desc',
        ];

        yield [
            [
                'identifier' => 'id',
                'description' => '',
            ],
            'id',
            '',
        ];
    }

    /**
     * @throws Throwable
     */
    #[DataProvider('validProvider')]
    public function testDecode(array $data, string $identifier, string $description): void
    {
        $json = json_encode($data);
        $subPurpose = (new JSONDecoder())->decode($json)->decodeObject(SubPurpose::class);
        $this->assertEquals($identifier, $subPurpose->identifier);
        $this->assertEquals($description, $subPurpose->description);
    }

    public static function invalidProvider(): Generator
    {
        yield 'missing-description' => [
            [
                'identifier' => 'identifier',
            ],
        ];

        yield 'missing-identifier' => [
            [
                'description' => 'description',
            ],
        ];

        yield 'invalid-identifier-type' => [
            [
                'identifier' => 1,
                'description' => 'description',
            ],
        ];

        yield 'invalid-description-type' => [
            [
                'identifier' => 'identifier',
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
        (new JSONDecoder())->decode($json)->decodeObject(SubPurpose::class);
    }
}
