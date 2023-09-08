<?php

declare(strict_types=1);

namespace Tests\Unit\Schema\Types;

use App\Schema\Types\FloatType;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;

#[Group('schema')]
#[Group('schema-type')]
class FloatTypeTest extends UnitTestCase
{
    private FloatType $type;

    protected function setUp(): void
    {
        parent::setUp();

        $this->type = new FloatType();
    }

    public function testScalarType(): void
    {
        $this->assertEquals('float', $this->type->getScalarType());
    }

    public function testTypeScriptAnnotationType(): void
    {
        $this->assertEquals('number', $this->type->getTypeScriptAnnotationType());
    }
}
