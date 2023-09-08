<?php

declare(strict_types=1);

namespace Tests\Unit\Schema;

use App\Schema\Entity;
use App\Schema\Fields\SchemaVersionField;
use App\Schema\Schema;
use App\Schema\Types\IntType;
use Generator;
use InvalidArgumentException;
use MinVWS\Codable\Decoder;
use MinVWS\Codable\Encoder;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;
use Throwable;

use function array_key_exists;
use function sprintf;

#[Group('schema')]
#[Group('schema-entity')]
final class EntityTest extends UnitTestCase
{
    private static Schema $schema;

    public static function versionAndFieldProvider(): Generator
    {
        for ($v = self::getSchema()->getMinVersion()->getVersion(); $v <= self::getSchema()->getMaxVersion()->getVersion(); $v++) {
            foreach (self::getSchema()->getFields() as $field) {
                if ($field instanceof SchemaVersionField) {
                    continue;
                }

                $exists = $field->isInVersion($v);
                $label = "v{$v} - " . $field->getName() . ' (' . ($exists ? 'exists' : "doesn't exist") . ')';
                yield $label => [$v, $field->getName(), $exists];
            }
        }
    }

    #[DataProvider('versionAndFieldProvider')]
    public function testEncoding(int $version, string $field, bool $exists): void
    {
        $object = self::getSchema()->getVersion($version)->newInstance();
        if ($exists) {
            $object->$field = 42;
        }

        $encoder = new Encoder();
        $encoder->getContext()->setUseAssociativeArraysForObjects(true);
        $data = $encoder->encode($object);

        $this->assertEquals($exists, array_key_exists($field, $data));
        if ($exists) {
            $this->assertEquals(42, $data[$field]);
        }
    }

    #[DataProvider('versionAndFieldProvider')]
    public function testDecoding(int $version, string $field, bool $exists): void
    {
        $data = [
            'version1Up' => 1,
            'version1UpTo1' => 2,
            'version1UpTo2' => 3,
            'version2Up' => 4,
            'version2UpTo2' => 5,
            'version3Up' => 6,
        ];

        $decoder = new Decoder();
        $container = $decoder->decode($data);
        $object = self::getSchema()->getVersion($version)->decode($container);

        $this->assertEquals($exists, isset($object->$field));
        if ($exists) {
            $this->assertEquals($data[$field], $object->$field);
        }
    }

    #[DataProvider('versionAndFieldProvider')]
    public function testMagicAttributes(int $version, string $field, bool $exists): void
    {
        $object = self::getSchema()->getVersion($version)->newInstance();

        $this->assertFalse(isset($object->$field));

        if ($exists) {
            $this->assertNull($object->$field);
            $object->$field = 42;
            $this->assertEquals(42, $object->$field);
        } else {
            try {
                $value = $object->$field;
                $this->fail(sprintf('It should be impossible to get the value (%s) of this property: %s', $value, $field));
            } catch (Throwable $e) {
                $this->assertTrue($e instanceof InvalidArgumentException);
            }

            try {
                $object->$field = 42;
                $this->fail(sprintf('It should be impossible to set a value to this property: %s', $field));
            } catch (Throwable $e) {
                $this->assertTrue($e instanceof InvalidArgumentException);
            }
        }

        $this->assertEquals($exists, isset($object->$field));
    }

    public static function getSchema(): Schema
    {
        if (isset(self::$schema)) {
            return self::$schema;
        }

        $schema = new Schema(Entity::class);

        $schema->setCurrentVersion(3);

        $schema->add(IntType::createField('version1Up'));
        $schema->add(IntType::createField('version1UpTo1'))->setMaxVersion(1);
        $schema->add(IntType::createField('version1UpTo2'))->setMaxVersion(2);
        $schema->add(IntType::createField('version2Up'))->setMinVersion(2);
        $schema->add(IntType::createField('version2UpTo2'))->setMinVersion(2)->setMaxVersion(2);
        $schema->add(IntType::createField('version3Up'))->setMinVersion(3);

        return self::$schema = $schema;
    }
}
