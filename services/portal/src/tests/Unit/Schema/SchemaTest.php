<?php

declare(strict_types=1);

namespace Tests\Unit\Schema;

use App\Schema\Entity;
use App\Schema\Generator\Base\VersionClassCollector;
use App\Schema\Generator\Base\VersionInterface;
use App\Schema\Generator\Base\VersionInterfaceCollector;
use App\Schema\Schema;
use App\Schema\Types\IntType;
use Generator;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use stdClass;
use Tests\Unit\UnitTestCase;

use function array_map;
use function sort;

#[Group('schema')]
class SchemaTest extends UnitTestCase
{
    private Schema $schema;

    protected function setUp(): void
    {
        parent::setUp();

        $schema = new Schema(stdClass::class);

        $schema->setCurrentVersion(3);
        $schema->setUseVersionedClasses(true);
        $schema->setVersionedNamespace('Test\\Versions\\stdClass');

        $schema->add(IntType::createField('version1Up'));
        $schema->add(IntType::createField('version1UpTo1'))->setMaxVersion(1);
        $schema->add(IntType::createField('version1UpTo2'))->setMaxVersion(2);
        $schema->add(IntType::createField('version2Up'))->setMinVersion(2);
        $schema->add(IntType::createField('version2UpTo2'))->setMinVersion(2)->setMaxVersion(2);
        $schema->add(IntType::createField('version3Up'))->setMinVersion(3);

        $this->schema = $schema;
    }

    private function assertMinMaxCurrentVersion(Schema $schema, int $minVersion, int $maxVersion, int $currentVersion): void
    {
        $this->assertEquals($minVersion, $schema->getMinVersion()->getVersion());
        $this->assertEquals($maxVersion, $schema->getMaxVersion()->getVersion());
        $this->assertEquals($currentVersion, $schema->getCurrentVersion()->getVersion());
    }

    public function testMinMaxCurrentVersion(): void
    {
        $this->assertMinMaxCurrentVersion($this->schema, 1, 3, 3);

        $schema = new Schema(stdClass::class);
        $this->assertMinMaxCurrentVersion($schema, 1, 1, 1);

        $schema->add(IntType::createField('version1Up'));
        $this->assertMinMaxCurrentVersion($schema, 1, 1, 1);

        $schema->add(IntType::createField('version1UpTo1'))->setMaxVersion(1);
        $this->assertMinMaxCurrentVersion($schema, 1, 2, 1);

        $schema->add(IntType::createField('version2Up'))->setMinVersion(2);
        $this->assertMinMaxCurrentVersion($schema, 1, 2, 1);

        $schema->add(IntType::createField('version3Up'))->setMinVersion(3);
        $this->assertMinMaxCurrentVersion($schema, 1, 3, 1);

        $schema->setCurrentVersion(3);
        $this->assertMinMaxCurrentVersion($schema, 1, 3, 3);

        $schema->setCurrentVersion(4);
        $this->assertMinMaxCurrentVersion($schema, 1, 4, 4);
    }

    public static function fieldAvailabilityDataProvider(): Generator
    {
        yield "Version 1 / version1Up" => [1, 'version1Up', true];
        yield "Version 1 / version1UpTo1" => [1, 'version1UpTo1', true];
        yield "Version 1 / version1UpTo2" => [1, 'version1UpTo2', true];
        yield "Version 1 / version2Up" => [1, 'version2Up', false];
        yield "Version 1 / version2UpTo2" => [1, 'version2UpTo2', false];
        yield "Version 1 / version3Up" => [1, 'version3Up', false];

        yield "Version 2 / version1Up" => [2, 'version1Up', true];
        yield "Version 2 / version1UpTo1" => [2, 'version1UpTo1', false];
        yield "Version 2 / version1UpTo2" => [2, 'version1UpTo2', true];
        yield "Version 2 / version2Up" => [2, 'version2Up', true];
        yield "Version 2 / version2UpTo2" => [2, 'version2UpTo2', true];
        yield "Version 2 / version3Up" => [2, 'version3Up', false];

        yield "Version 3 / version1Up" => [3, 'version1Up', true];
        yield "Version 3 / version1UpTo1" => [3, 'version1UpTo1', false];
        yield "Version 3 / version1UpTo2" => [3, 'version1UpTo2', false];
        yield "Version 3 / version2Up" => [3, 'version2Up', true];
        yield "Version 3 / version2UpTo2" => [3, 'version2UpTo2', false];
        yield "Version 3 / version3Up" => [3, 'version3Up', true];
    }

    #[DataProvider('fieldAvailabilityDataProvider')]
    public function testFieldAvailability(?int $version, string $field, bool $expected): void
    {
        $schemaVersion = $this->schema->getVersion($version);
        if ($expected) {
            $this->assertNotNull($schemaVersion->getField($field));
        } else {
            $this->assertNull($schemaVersion->getField($field));
        }
    }

    public function testVersionInterfaceCollector(): void
    {
        $interfaceCollector = new VersionInterfaceCollector($this->schema);

        $this->assertCount(6, $interfaceCollector->getAll());
        $this->assertEquals(
            ['stdClassCommon', 'stdClassV1UpTo1', 'stdClassV1UpTo2', 'stdClassV2Up', 'stdClassV2UpTo2', 'stdClassV3Up'],
            $interfaceCollector->getAllShortNames(),
        );
    }

    public function testVersionClassCollector(): void
    {
        $interfaceCollector = new VersionInterfaceCollector($this->schema);
        $classCollector = new VersionClassCollector($this->schema, $interfaceCollector->getAll());

        $this->assertCount(3, $classCollector->getAll());
        $this->assertEquals(
            ['stdClassV1', 'stdClassV2', 'stdClassV3'],
            $classCollector->getAllShortNames(),
        );
    }

    public static function interfaceFieldsProvider(): Generator
    {
        yield "Common" => ['stdClassCommon', ['version1Up'], []];
        yield "V1UpTo1" => ['stdClassV1UpTo1', ['version1UpTo1'], []];
        yield "V1UpTo2" => ['stdClassV1UpTo2', ['version1UpTo2'], []];
        yield "V2Up" => ['stdClassV2Up', ['version2Up'], ['stdClassCommon']];
        yield "V2UpTo2" => ['stdClassV2UpTo2', ['version2UpTo2'], []];
        yield "V3Up" => ['stdClassV3Up', ['version3Up'], ['stdClassV2Up']];
    }

    /**
     * @param array $expectedFieldNames
     */
    #[DataProvider('interfaceFieldsProvider')]
    public function testInterfaceFields(string $shortName, array $expectedFieldNames, array $expectedInterfaceShortNames): void
    {
        $versionCollector = new VersionInterfaceCollector($this->schema);

        $interface = $versionCollector->getByShortName($shortName);
        $this->assertNotNull($interface);

        $fieldNames = $interface->getFieldNames();
        sort($fieldNames);
        sort($expectedFieldNames);
        $this->assertEquals($expectedFieldNames, $fieldNames);

        $interfaceShortNames = array_map(static fn (VersionInterface $i) => $i->getShortName(), $interface->getInterfaces());
        sort($interfaceShortNames);
        sort($expectedInterfaceShortNames);
        $this->assertEquals($expectedInterfaceShortNames, $interfaceShortNames);
    }

    public function testItDoesNotSetFieldNameToOwnerForNonEntityClasses(): void
    {
        $schema = new Schema(stdClass::class);

        $this->assertNull($schema->getOwnerFieldName());
    }

    public function testItSetsOwnerFieldNameToOwner(): void
    {
        $mockedEntityClass = Mockery::mock(Entity::class)::class;
        $schema = new Schema($mockedEntityClass);

        $this->assertEquals('owner', $schema->getOwnerFieldName());
    }
}
