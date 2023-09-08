<?php

declare(strict_types=1);

namespace Tests\Unit\Schema;

use App\Models\Purpose\Purpose;
use App\Models\Purpose\SubPurpose;
use App\Schema\Purpose\PurposeSpecificationConfig;
use App\Schema\Purpose\PurposeSpecificationException;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('schema')]
#[Group('schema-purpose')]
class PurposeSpecificationConfigTest extends TestCase
{
    private ?PurposeSpecificationConfig $config;

    protected function setUp(): void
    {
        parent::setUp();

        $this->config = PurposeSpecificationConfig::getConfig();
    }

    protected function tearDown(): void
    {
        PurposeSpecificationConfig::setConfig($this->config);

        parent::tearDown();
    }

    public function testDefaultConfig(): void
    {
        $this->assertIsObject(PurposeSpecificationConfig::getConfig());
        $this->assertEquals(Purpose::class, PurposeSpecificationConfig::getConfig()->getPurposeType());
        $this->assertEquals(SubPurpose::class, PurposeSpecificationConfig::getConfig()->getSubPurposeType());
    }

    public function testConfigShouldNotBeNull(): void
    {
        PurposeSpecificationConfig::setConfig(null);
        $this->expectException(PurposeSpecificationException::class);
        PurposeSpecificationConfig::getConfig();
    }
}
