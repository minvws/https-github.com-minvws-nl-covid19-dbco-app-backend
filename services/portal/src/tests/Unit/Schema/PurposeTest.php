<?php

declare(strict_types=1);

namespace Tests\Unit\Schema;

use App\Schema\Entity;
use App\Schema\Purpose\PurposeDetail;
use App\Schema\Purpose\PurposeSpecificationBuilder;
use App\Schema\Purpose\PurposeSpecificationConfig;
use App\Schema\Purpose\PurposeSpecificationException;
use App\Schema\Schema;
use App\Schema\Types\SchemaVersionType;
use App\Schema\Types\StringType;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

use function array_map;

#[Group('schema')]
#[Group('schema-purpose')]
class PurposeTest extends TestCase
{
    private ?PurposeSpecificationConfig $orgPurposeSpecificationConfig = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orgPurposeSpecificationConfig = PurposeSpecificationConfig::getConfig();
        $config = new PurposeSpecificationConfig(TestPurpose::class, TestSubPurpose::class);
        PurposeSpecificationConfig::setConfig($config);
    }

    protected function tearDown(): void
    {
        PurposeSpecificationConfig::setConfig($this->orgPurposeSpecificationConfig);

        parent::tearDown();
    }

    public function testFieldPurposeSpecs(): void
    {
        $schema = new Schema(Entity::class);
        $schema->setCurrentVersion(1);

        $firstnameField = $schema->add(StringType::createField('firstname'));
        $firstnameField->specifyPurpose(static function (PurposeSpecificationBuilder $builder): void {
            $builder
                ->addPurpose(TestPurpose::PurposeB, TestSubPurpose::SubPurposeA)
                ->addPurpose(TestPurpose::PurposeC, TestSubPurpose::SubPurposeB);
        });

        $this->assertFalse($firstnameField->getPurposeSpecification()->hasPurpose(TestPurpose::PurposeA));
        $this->assertTrue($firstnameField->getPurposeSpecification()->hasPurpose(TestPurpose::PurposeB));
        $this->assertTrue($firstnameField->getPurposeSpecification()->hasPurpose(TestPurpose::PurposeC));
        $this->assertEquals(
            TestSubPurpose::SubPurposeA,
            $firstnameField->getPurposeSpecification()->getPurposeDetail(TestPurpose::PurposeB)->subPurpose,
        );
        $this->assertEquals(
            TestSubPurpose::SubPurposeB,
            $firstnameField->getPurposeSpecification()->getPurposeDetail(TestPurpose::PurposeC)->subPurpose,
        );
    }

    public function testSchemaInheritedPurposeSpecs(): void
    {
        $config = new PurposeSpecificationConfig(TestPurpose::class, TestSubPurpose::class);
        PurposeSpecificationConfig::setConfig($config);

        $schema = new Schema(Entity::class);
        $schema->setCurrentVersion(1);
        $schema->specifyPurpose(static function (PurposeSpecificationBuilder $builder): void {
            $builder
                ->addPurpose(TestPurpose::PurposeA, TestSubPurpose::SubPurposeA)
                ->addPurpose(TestPurpose::PurposeB, TestSubPurpose::SubPurposeB);
        });

        $firstnameField = $schema->add(StringType::createField('firstname'));
        $lastnameField = $schema->add(StringType::createField('lastname'));
        $lastnameField->specifyPurpose(static function (PurposeSpecificationBuilder $builder): void {
            $builder
                ->addPurpose(TestPurpose::PurposeB, TestSubPurpose::SubPurposeA)
                ->addPurpose(TestPurpose::PurposeC, TestSubPurpose::SubPurposeA);
        });

        $this->assertTrue($firstnameField->getPurposeSpecification()->hasPurpose(TestPurpose::PurposeA));
        $this->assertTrue($firstnameField->getPurposeSpecification()->hasPurpose(TestPurpose::PurposeB));
        $this->assertFalse($firstnameField->getPurposeSpecification()->hasPurpose(TestPurpose::PurposeC));
        $this->assertEquals(
            TestSubPurpose::SubPurposeB,
            $firstnameField->getPurposeSpecification()->getPurposeDetail(TestPurpose::PurposeB)->subPurpose,
        );
        $this->assertCount(2, $firstnameField->getPurposeSpecification()->getAllPurposeDetails());
        $this->assertEquals(
            [TestSubPurpose::SubPurposeA, TestSubPurpose::SubPurposeB],
            array_map(static fn (PurposeDetail $d) => $d->subPurpose, $firstnameField->getPurposeSpecification()->getAllPurposeDetails()),
        );

        $this->assertFalse($lastnameField->getPurposeSpecification()->hasPurpose(TestPurpose::PurposeA));
        $this->assertTrue($lastnameField->getPurposeSpecification()->hasPurpose(TestPurpose::PurposeB));
        $this->assertTrue($lastnameField->getPurposeSpecification()->hasPurpose(TestPurpose::PurposeC));
        $this->assertEquals(
            TestSubPurpose::SubPurposeA,
            $lastnameField->getPurposeSpecification()->getPurposeDetail(TestPurpose::PurposeB)->subPurpose,
        );
        $this->assertCount(2, $lastnameField->getPurposeSpecification()->getAllPurposeDetails());

        $schemaVersionField = $schema->getSchemaVersionField();
        $this->assertNotNull($schemaVersionField);
        $this->assertEquals(
            TestSubPurpose::SubPurposeB,
            $schemaVersionField->getPurposeSpecification()->getPurposeDetail(TestPurpose::PurposeB)->subPurpose,
        );
    }

    public function testTypeBasedSubPurposeOverride(): void
    {
        $config = new PurposeSpecificationConfig(TestPurpose::class, TestSubPurpose::class);
        $config->setFallbackSubPurposeOverrideForType(SchemaVersionType::class, TestSubPurpose::SubPurposeC);
        PurposeSpecificationConfig::setConfig($config);

        $schema = new Schema(Entity::class);
        $schema->setCurrentVersion(1);
        $schema->specifyPurpose(static function (PurposeSpecificationBuilder $builder): void {
            $builder
                ->addPurpose(TestPurpose::PurposeA, TestSubPurpose::SubPurposeA)
                ->addPurpose(TestPurpose::PurposeB, TestSubPurpose::SubPurposeB);
        });

        $firstnameField = $schema->add(StringType::createField('firstname'));
        $schemaVersionField = $schema->getSchemaVersionField();
        $this->assertNotNull($schemaVersionField);
        $this->assertEquals(
            TestSubPurpose::SubPurposeB,
            $firstnameField->getPurposeSpecification()->getPurposeDetail(TestPurpose::PurposeB)->subPurpose,
        );
        $this->assertEquals(
            TestSubPurpose::SubPurposeC,
            $schemaVersionField->getPurposeSpecification()->getPurposeDetail(TestPurpose::PurposeB)->subPurpose,
        );
    }

    public function testOnlyAllowOneTimePurposeSpecification(): void
    {
        $schema = new Schema(Entity::class);
        $schema->setCurrentVersion(1);
        $firstnameField = $schema->add(StringType::createField('firstname'));

        $firstnameField->specifyPurpose(static function (PurposeSpecificationBuilder $builder): void {
            $builder
                ->addPurpose(TestPurpose::PurposeA, TestSubPurpose::SubPurposeA)
                ->addPurpose(TestPurpose::PurposeB, TestSubPurpose::SubPurposeB);
        });

        $this->expectException(PurposeSpecificationException::class);

        $firstnameField->specifyPurpose(static function (PurposeSpecificationBuilder $builder): void {
            $builder
                ->addPurpose(TestPurpose::PurposeA, TestSubPurpose::SubPurposeA)
                ->addPurpose(TestPurpose::PurposeB, TestSubPurpose::SubPurposeB);
        });
    }

    public function testDefaultEmptyPurposeSpecification(): void
    {
        $schema = new Schema(Entity::class);
        $schema->setCurrentVersion(1);
        $firstnameField = $schema->add(StringType::createField('firstname'));

        $this->assertCount(0, $firstnameField->getPurposeSpecification()->getAllPurposeDetails());
    }
}
