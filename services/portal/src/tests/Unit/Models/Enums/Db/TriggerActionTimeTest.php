<?php

declare(strict_types=1);

namespace Tests\Unit\Models\Enums\Db;

use App\Models\Enums\Db\TriggerActionTime;
use Tests\Unit\UnitTestCase;

class TriggerActionTimeTest extends UnitTestCase
{
    public function testStatement(): void
    {
        $this->assertEquals('BEFORE INSERT', TriggerActionTime::BEFORE_INSERT->statement());
        $this->assertEquals('AFTER INSERT', TriggerActionTime::AFTER_INSERT->statement());
        $this->assertEquals('BEFORE UPDATE', TriggerActionTime::BEFORE_UPDATE->statement());
        $this->assertEquals('AFTER UPDATE', TriggerActionTime::AFTER_UPDATE->statement());
    }

    public function testAlias(): void
    {
        $this->assertEquals('bi', TriggerActionTime::BEFORE_INSERT->alias());
        $this->assertEquals('ai', TriggerActionTime::AFTER_INSERT->alias());
        $this->assertEquals('bu', TriggerActionTime::BEFORE_UPDATE->alias());
        $this->assertEquals('au', TriggerActionTime::AFTER_UPDATE->alias());
    }
}
