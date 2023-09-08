<?php

declare(strict_types=1);

namespace Tests\Unit\Schema\JSONSchema\Diff\Model;

use App\Schema\Generator\JSONSchema\Diff\Model\DiffType;
use MinVWS\Codable\Encoder;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;

use function strtolower;

#[Group('schema-jsonschema-diff')]
class DiffTypeTest extends UnitTestCase
{
    public function testEncode(): void
    {
        foreach (DiffType::cases() as $diffType) {
            $this->assertEquals(strtolower($diffType->name), (new Encoder())->encode($diffType));
        }
    }
}
