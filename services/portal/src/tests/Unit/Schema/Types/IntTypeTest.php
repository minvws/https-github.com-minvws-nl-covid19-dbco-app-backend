<?php

declare(strict_types=1);

namespace Tests\Unit\Schema\Types;

use App\Schema\Types\IntType;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;

#[Group('schema')]
#[Group('schema-type')]
class IntTypeTest extends UnitTestCase
{
    private IntType $type;

    protected function setUp(): void
    {
        parent::setUp();

        $this->type = new IntType();
    }

    public function testScalarType(): void
    {
        $this->assertEquals('int', $this->type->getScalarType());
    }

    public function testTypeScriptAnnotationType(): void
    {
        $this->assertEquals('number', $this->type->getTypeScriptAnnotationType());
    }
}
