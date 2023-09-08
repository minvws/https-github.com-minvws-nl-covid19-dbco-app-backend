<?php

declare(strict_types=1);

namespace Tests\Unit\Schema\JSONSchema\Diff\Schema;

use App\Schema\Generator\JSONSchema\Diff\Schema\Purpose;
use Generator;
use MinVWS\Codable\CodableException;
use MinVWS\Codable\JSONDecoder;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;
use Throwable;

use function json_encode;

#[Group('schema-jsonschema-diff')]
class PurposeTest extends UnitTestCase
{
    public static function validProvider(): Generator
    {
        yield [
            [
                'purposeIdentifier' => [
                    'description' => 'purposeDescription',
                    'subPurpose' => [
                        'identifier' => 'subPurposeIdentifier',
                        'description' => 'subPurposeDescription',
                    ],
                ],
            ],
            'purposeIdentifier',
            'purposeDescription',
            'subPurposeIdentifier',
            'subPurposeDescription',
        ];

        yield [
            [
                'purposeIdentifier' => [
                    'description' => '',
                    'subPurpose' => [
                        'identifier' => 'subPurposeIdentifier',
                        'description' => '',
                    ],
                ],
            ],
            'purposeIdentifier',
            '',
            'subPurposeIdentifier',
            '',
        ];
    }

    /**
     * @throws Throwable
     */
    #[DataProvider('validProvider')]
    public function testDecode(array $data, string $purposeIdentifier, string $purposeDescription, string $subPurposeIdentifier, string $subPurposeDescription): void
    {
        $json = json_encode($data);
        $purposes = (new JSONDecoder())->decode($json)->decodeArray(Purpose::class);
        $this->assertCount(1, $purposes);
        $this->assertArrayHasKey($purposeIdentifier, $purposes);
        $purpose = $purposes[$purposeIdentifier];
        $this->assertInstanceOf(Purpose::class, $purpose);
        $this->assertEquals($purposeIdentifier, $purpose->identifier);
        $this->assertEquals($purposeDescription, $purpose->description);
        $this->assertEquals($subPurposeIdentifier, $purpose->subPurpose->identifier);
        $this->assertEquals($subPurposeDescription, $purpose->subPurpose->description);
    }

    public static function invalidProvider(): Generator
    {
        yield 'missing-description' => [
            [
                'purposeIdentifier' => [
                    'subPurpose' => [
                        'identifier' => 'subPurposeIdentifier',
                        'description' => 'subPurposeDescription',
                    ],
                ],
            ],
        ];

        yield 'missing-subpurpose' => [
            [
                'purposeIdentifier' => [
                    'description' => 'purposeDescription',
                ],
            ],
        ];


        yield 'invalid-description-type' => [
            [
                'purposeIdentifier' => [
                    'description' => 1,
                    'subPurpose' => [
                        'identifier' => 'subPurposeIdentifier',
                        'description' => 'subPurposeDescription',
                    ],
                ],
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
        (new JSONDecoder())->decode($json)->decodeArray(Purpose::class);
    }
}
