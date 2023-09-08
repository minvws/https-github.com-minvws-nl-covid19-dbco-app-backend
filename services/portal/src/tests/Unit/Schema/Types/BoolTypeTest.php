<?php

declare(strict_types=1);

namespace Tests\Unit\Schema\Types;

use App\Schema\Types\BoolType;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;

#[Group('schema')]
#[Group('schema-type')]
class BoolTypeTest extends UnitTestCase
{
    private BoolType $type;

    protected function setUp(): void
    {
        parent::setUp();

        $this->type = new BoolType();
    }

    public function testScalarType(): void
    {
        $this->assertEquals('bool', $this->type->getScalarType());
    }

    public function testTypeScriptAnnotationType(): void
    {
        $this->assertEquals('boolean', $this->type->getTypeScriptAnnotationType());
    }
}
