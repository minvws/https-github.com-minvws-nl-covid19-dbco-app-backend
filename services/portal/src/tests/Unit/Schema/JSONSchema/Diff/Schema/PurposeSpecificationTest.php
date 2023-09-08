<?php

declare(strict_types=1);

namespace Tests\Unit\Schema\JSONSchema\Diff\Schema;

use App\Schema\Generator\JSONSchema\Diff\Schema\PurposeSpecification;
use Generator;
use MinVWS\Codable\CodableException;
use MinVWS\Codable\JSONDecoder;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;
use Throwable;

use function count;
use function json_encode;

#[Group('schema-jsonschema-diff')]
class PurposeSpecificationTest extends UnitTestCase
{
    public static function validProvider(): Generator
    {
        yield [
            [],
            null,
            0,
        ];

        yield [
            [
                'remark' => null,
                'purposes' => [],
            ],
            null,
            0,
        ];

        yield [
            [
                'remark' => 'This is a remark',
                'purposes' => [
                    'purpose1' => [
                        'description' => 'Purpose 1',
                        'subPurpose' => [
                            'identifier' => 'subPurpose1',
                            'description' => 'Sub purpose 1',
                        ],
                    ],
                ],
            ],
            'This is a remark',
            1,
        ];

        yield [
            [
                'remark' => null,
                'purposes' => [
                    'purpose1' => [
                        'description' => 'Purpose 1',
                        'subPurpose' => [
                            'identifier' => 'subPurpose1',
                            'description' => 'Sub purpose 1',
                        ],
                    ],
                    'purpose2' => [
                        'description' => 'Purpose 2',
                        'subPurpose' => [
                            'identifier' => 'subPurpose1',
                            'description' => 'Sub purpose 1',
                        ],
                    ],
                ],
            ],
            null,
            2,
        ];
    }

    /**
     * @throws Throwable
     */
    #[DataProvider('validProvider')]
    public function testDecode(array $data, ?string $remark, ?int $purposeCount): void
    {
        $json = json_encode($data);
        $spec = (new JSONDecoder())->decode($json)->decodeObject(PurposeSpecification::class);
        $this->assertEquals($remark, $spec->remark);
        $this->assertEquals($purposeCount, count($spec->purposes));
    }

    public static function invalidProvider(): Generator
    {
        yield 'invalid-remark-type' => [
            [
                'remark' => 1,
            ],
        ];

        yield 'invalid-purposes->type' => [
            [
                'purposes' => 'purposes',
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
        (new JSONDecoder())->decode($json)->decodeObject(PurposeSpecification::class);
    }
}
