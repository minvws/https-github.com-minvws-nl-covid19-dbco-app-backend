<?php

declare(strict_types=1);

namespace Tests\Unit\Schema;

use App\Schema\Entity;
use App\Schema\Schema;
use App\Schema\Types\DateTimeType;
use App\Schema\Types\IntType;
use App\Schema\Types\StringType;
use App\Schema\Update\Update;
use App\Schema\Update\UpdateException;
use DateTimeImmutable;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

use function array_diff;
use function array_keys;

#[Group('schema')]
#[Group('schema-update')]
class UpdateTest extends TestCase
{
    private Schema $schema;

    protected function setUp(): void
    {
        parent::setUp();

        $schema = new Schema(Entity::class);
        $schema->add(IntType::createField('intField'));
        $schema->add(StringType::createField('stringField'));
        $schema->add(DateTimeType::createField('dateTimeField', 'Y-m-d'));

        $this->schema = $schema;
    }

    public static function updateProvider(): Generator
    {
        yield 'nothing' => [
            ['intField' => 1234, 'stringField' => 'lorem ipsum', 'dateTimeField' => new DateTimeImmutable('2021-02-01')],
            [],
            null,
            0,
            [],
            ['intField' => 1234, 'stringField' => 'lorem ipsum', 'dateTimeField' => new DateTimeImmutable('2021-02-01')],
        ];

        yield 'only unchanged data' => [
            ['intField' => 1234, 'stringField' => 'lorem ipsum', 'dateTimeField' => new DateTimeImmutable('2021-02-01')],
            ['intField' => 1234, 'stringField' => 'lorem ipsum', 'dateTimeField' => '2021-02-01'],
            null,
            0,
            [],
            ['intField' => 1234, 'stringField' => 'lorem ipsum', 'dateTimeField' => new DateTimeImmutable('2021-02-01')],
        ];

        yield 'a few fields' => [
            ['intField' => 1234, 'stringField' => 'lorem ipsum', 'dateTimeField' => new DateTimeImmutable('2021-02-01')],
            ['intField' => 4567, 'stringField' => 'dolor sit amet'],
            null,
            2,
            ['intField', 'stringField'],
            ['intField' => 4567, 'stringField' => 'dolor sit amet', 'dateTimeField' => new DateTimeImmutable('2021-02-01')],
        ];

        yield 'a few fields with some unchanged data' => [
            ['intField' => 1234, 'stringField' => 'lorem ipsum', 'dateTimeField' => new DateTimeImmutable('2021-02-01')],
            ['intField' => 4567, 'stringField' => 'dolor sit amet', 'dateTimeField' => '2021-02-01'],
            null,
            2,
            ['intField', 'stringField'],
            ['intField' => 4567, 'stringField' => 'dolor sit amet', 'dateTimeField' => new DateTimeImmutable('2021-02-01')],
        ];

        yield 'changed data for all fields' => [
            ['intField' => 1234, 'stringField' => 'lorem ipsum', 'dateTimeField' => new DateTimeImmutable('2021-02-01')],
            ['intField' => 4567, 'stringField' => 'dolor sit amet', 'dateTimeField' => '2021-03-01'],
            null,
            3,
            ['intField', 'stringField', 'dateTimeField'],
            ['intField' => 4567, 'stringField' => 'dolor sit amet', 'dateTimeField' => new DateTimeImmutable('2021-03-01')],
        ];

        yield 'changed data for all fields with some nulls' => [
            ['intField' => 1234, 'stringField' => 'lorem ipsum', 'dateTimeField' => new DateTimeImmutable('2021-02-01')],
            ['intField' => null, 'stringField' => 'dolor sit amet', 'dateTimeField' => null],
            null,
            3,
            ['intField', 'stringField', 'dateTimeField'],
            ['intField' => null, 'stringField' => 'dolor sit amet', 'dateTimeField' => null],
        ];

        yield 'explicit field' => [
            ['intField' => 1234, 'stringField' => 'lorem ipsum', 'dateTimeField' => new DateTimeImmutable('2021-02-01')],
            ['intField' => 4567, 'stringField' => 'dolor sit amet', 'dateTimeField' => '2021-03-01'],
            ['intField'],
            1,
            ['intField'],
            ['intField' => 4567, 'stringField' => 'lorem ipsum', 'dateTimeField' => new DateTimeImmutable('2021-02-01')],
        ];

        yield 'explicit fields but with only 1 field that really changed' => [
            ['intField' => 1234, 'stringField' => 'lorem ipsum', 'dateTimeField' => new DateTimeImmutable('2021-02-01')],
            ['intField' => 4567, 'stringField' => 'lorem ipsum', 'dateTimeField' => '2021-03-01'],
            ['intField', 'stringField'],
            1,
            ['intField'],
            ['intField' => 4567, 'stringField' => 'lorem ipsum', 'dateTimeField' => new DateTimeImmutable('2021-02-01')],
        ];
    }

    #[DataProvider('updateProvider')]
    public function testUpdate(array $initialData, array $updateData, ?array $updateFields, int $diffCount, array $updatedFields, array $finalData): void
    {
        $object = $this->schema->getCurrentVersion()->newInstance();
        $object->intField = $initialData['intField'];
        $object->stringField = $initialData['stringField'];
        $object->dateTimeField = $initialData['dateTimeField'];

        $update = new Update($this->schema->getCurrentVersion(), $updateData);
        if ($updateFields !== null) {
            $update->setFields($updateFields);
        }

        $this->assertTrue($update->validate()->isValid());

        $diff = $update->getDiff($object);

        $this->assertCount($diffCount, $diff->getFieldDiffs());

        foreach ($updatedFields as $fieldName) {
            $fieldDiff = $diff->getFieldDiff($fieldName);
            $this->assertNotNull($fieldDiff);
            $this->assertEquals($initialData[$fieldName], $fieldDiff->getOldValue());
            $this->assertEquals($finalData[$fieldName], $fieldDiff->getNewValue());
        }

        $unchangedFields = array_diff(array_keys($this->schema->getCurrentVersion()->getFields()), $updatedFields);
        foreach ($unchangedFields as $fieldName) {
            $this->assertNull($diff->getFieldDiff($fieldName));
        }

        $update->apply($object);

        foreach ($finalData as $fieldName => $value) {
            $this->assertEquals($value, $object->$fieldName);
        }
    }

    public function testValidation(): void
    {
        $object = $this->schema->getCurrentVersion()->newInstance();
        $object->intField = 1234;
        $object->stringField = 'lorem ipsum';
        $object->dateTimeField = new DateTimeImmutable('2021-02-01');

        $update = new Update($this->schema->getCurrentVersion(), ['stringField' => 1234]);
        $this->assertFalse($update->validate()->isValid());
    }

    public function testDiffWithInvalidData(): void
    {
        $object = $this->schema->getCurrentVersion()->newInstance();
        $object->intField = 1234;
        $object->stringField = 'lorem ipsum';
        $object->dateTimeField = new DateTimeImmutable('2021-02-01');

        $update = new Update($this->schema->getCurrentVersion(), ['stringField' => 1234]);
        $this->expectException(UpdateException::class);
        $update->getDiff($object);
    }

    public function testApplyWithInvalidData(): void
    {
        $object = $this->schema->getCurrentVersion()->newInstance();
        $object->intField = 1234;
        $object->stringField = 'lorem ipsum';
        $object->dateTimeField = new DateTimeImmutable('2021-02-01');

        $update = new Update($this->schema->getCurrentVersion(), ['stringField' => 1234]);
        $this->expectException(UpdateException::class);
        $update->apply($object);
    }
}
