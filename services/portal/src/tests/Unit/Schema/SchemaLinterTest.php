<?php

declare(strict_types=1);

namespace Tests\Unit\Schema;

use App\Schema\Entity;
use App\Schema\Schema;
use App\Schema\SchemaLinter;
use App\Schema\SchemaProvider;
use App\Schema\Types\StringType;
use Faker\Factory as FakerFactory;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\Faker\Provider\SchemaVersions as SchemaVersionsProvider;
use Tests\Unit\UnitTestCase;

use function array_reverse;

class SchemaLinterTest extends UnitTestCase
{
    private readonly ?SchemaLinter $schemaLinter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker->addProvider(new SchemaVersionsProvider($this->faker));

        $this->schemaLinter = new SchemaLinter();
    }

    public function testLintClassesReturnsNoErrorsWithoutClasses(): void
    {
        $errors = $this->schemaLinter->lintClasses([]);
        $this->assertCount(0, $errors);
    }

    public function testLintClassesReturnsNoErrorsWithValidSchmema(): void
    {
        $entityStub = Mockery::mock(Entity::class);
        $schemaStub = Mockery::mock(SchemaProvider::class);
        $schemaStub->expects('getSchema')->andReturn(new Schema($entityStub::class));

        $errors = $this->schemaLinter->lintClasses([$schemaStub]);
        $this->assertCount(0, $errors);
    }

    public function testLintClassesThrowsForInvalidEntity(): void
    {
        $stub = Mockery::mock();
        $this->expectException(RuntimeException::class);
        $this->schemaLinter->lintClasses([$stub]);
    }

    public function testLintClassesReturnsErrorsWithValidSchmema(): void
    {
        $entityStub = Mockery::mock(Entity::class);
        $schemaStub = Mockery::mock(SchemaProvider::class);
        $schemaStub->expects('getSchema')->andReturn(new Schema($entityStub::class));

        $errors = $this->schemaLinter->lintClasses([$schemaStub]);
        $this->assertCount(0, $errors);
    }

    public function testValidateMinAndMaxAgainstCurrentVersionHasNoErrorsForEmptySchema(): void
    {
        $entityStub = Mockery::mock(Entity::class);
        $schema = new Schema($entityStub::class);
        $errors = $this->schemaLinter->validateFieldSpecifications($schema, "Test");
        $this->assertCount(0, $errors);
    }

    public function testValidateFieldMinSpecificationAgainstCurrentSchemaVersionWithSameVersion(): void
    {
        $version = $this->faker->numberBetween();
        $field = StringType::createField('testField')->setMinVersion($version);
        $errors = $this->schemaLinter->validateFieldMinSpecificationAgainstCurrentSchemaVersion($field, $version, "TestEntity");
        $this->assertCount(0, $errors);
    }

    public function testValidateFieldMinSpecificationAgainstCurrentSchemaVersionHasErrorsWhenMinIsGreaterThanCurrentVersion(): void
    {
        $fieldVersion = $this->faker->numberBetween(1);
        $field = StringType::createField('testField')->setMinVersion($fieldVersion);
        $schemaVersion = $this->faker->numberBetween(0, $fieldVersion);
        $errors = $this->schemaLinter->validateFieldMinSpecificationAgainstCurrentSchemaVersion($field, $schemaVersion, "TestEntity");
        $this->assertEquals(["TestEntity has field testField with higher min version than the schema itself"], $errors);
    }

    public function testValidateFieldMaxSpecificationAgainstCurrentSchemaVersionWithSameVersion(): void
    {
        $version = $this->faker->numberBetween();
        $field = StringType::createField('testField')->setMaxVersion($version);
        $errors = $this->schemaLinter->validateFieldMaxSpecificationAgainstCurrentSchemaVersion($field, $version, "TestEntity");
        $this->assertCount(0, $errors);
    }

    public function testValidateFieldMaxSpecificationAgainstCurrentSchemaVersionHasErrorsWhenMinIsGreaterThanCurrentVersion(): void
    {
        [$schemaVersion, $fieldVersion] = $this->faker->incrementalSchemaVersionRange();
        $field = StringType::createField('testField')->setMaxVersion($fieldVersion);
        $errors = $this->schemaLinter->validateFieldMaxSpecificationAgainstCurrentSchemaVersion($field, $schemaVersion, "TestEntity");
        $this->assertEquals(["TestEntity has field testField with higher max version than the schema itself"], $errors);
    }

    #[DataProvider('minMaxSpecificationProvider')]
    public function testValidateFieldMinMaxSpecification(int $min, int $max, array $expectedErrors): void
    {
        $field = StringType::createField('testField')->setMinVersion($min)->setMaxVersion($max);
        $errors = $this->schemaLinter->validateFieldMinMaxSpecification($field, "TestEntity");
        $this->assertEquals($expectedErrors, $errors);
    }

    public static function minMaxSpecificationProvider(): array
    {
        $faker = FakerFactory::create();
        $faker->addProvider(new SchemaVersionsProvider($faker));

        return [
            'min less than max' => [...$faker->incrementalSchemaVersionRange(), []],
            'min same as max' => [...$faker->identicalSchemaVersionRange(), []],
            'min greater than max' => [
                ...array_reverse(
                    $faker->incrementalSchemaVersionRange(),
                ),
                ['TestEntity has field testField with higher min version than it\'s own max']],
        ];
    }

    public function testValidateNoOverlappingFields(): void
    {
        $entityStub = Mockery::mock(Entity::class);
        $schema = new Schema($entityStub::class);
        $schema->add(StringType::createField('testField'));
        $schema->add(StringType::createField('testField'));
        $errors = $this->schemaLinter->validateNoOverlappingFields($schema, "TestEntity");
        $this->assertCount(1, $errors);
    }

    public function testValidateFieldsDontOverlapOnMaxAndMinWithValidExample(): void
    {
        $firstMax = $this->faker->numberBetween(1);
        $firstField = StringType::createField('testField')->setMaxVersion($firstMax);
        // Create a field with a version higher than or equal to the previous max
        $secondMin = $this->faker->numberBetween($firstMax);
        $secondField = StringType::createField('testField')->setMinVersion($secondMin);
        $fields = $this->schemaLinter->validateFieldsDontOverlapOnMaxAndMin($firstField, $secondField, "TestEntity");
        $this->assertCount(0, $fields);
    }

    public function testValidateFieldsDontOverlapOnMaxAndMinWithMinAndMax(): void
    {
        $firstMax = $this->faker->numberBetween(1);
        $firstField = StringType::createField('testField')->setMaxVersion($firstMax);
        // Create a field with a version lower than the previous max
        $secondMin = $this->faker->numberBetween(1, $firstMax);
        $secondField = StringType::createField('testField')->setMinVersion($secondMin);
        $this->assertEquals(
            ["TestEntity has field testField with overlapping specifications: A (1-$firstMax) & B ($secondMin-...)"],
            $this->schemaLinter->validateFieldsDontOverlapOnMaxAndMin($firstField, $secondField, "TestEntity"),
        );
    }

    public function testValidateFieldsDontOverlapOnMaxAndMinWithMinAndNoMax(): void
    {
        $firstField = StringType::createField('testField');
        $secondMin = $this->faker->numberBetween(1);
        $secondField = StringType::createField('testField')->setMinVersion($secondMin);
        $this->assertEquals(
            ["TestEntity has field testField with overlapping specifications: A (1-∞) & B ($secondMin-...)"],
            $this->schemaLinter->validateFieldsDontOverlapOnMaxAndMin($firstField, $secondField, "TestEntity"),
        );
    }
}
