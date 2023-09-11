<?php

declare(strict_types=1);

namespace Tests\Unit\Enum;

use App\Models\Catalog\Filter;
use Tests\Unit\UnitTestCase;

class FilterEnumTest extends UnitTestCase
{
    public function testItHasIdentifier(): void
    {
        $filter = Filter::All;
        $identifier = $filter->getidentifier();
        $this->assertEquals(Filter::All->value, $identifier);
    }

    public function testItHasLabel(): void
    {
        $filter = Filter::All;
        $label = $filter->getLabel();
        $this->assertEquals('Alle', $label);
    }
}
