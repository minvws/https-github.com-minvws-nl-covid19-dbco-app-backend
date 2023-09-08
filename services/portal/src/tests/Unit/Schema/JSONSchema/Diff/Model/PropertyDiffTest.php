<?php

declare(strict_types=1);

namespace Tests\Unit\Schema\JSONSchema\Diff\Model;

use App\Schema\Generator\JSONSchema\Diff\Model\DiffType;
use App\Schema\Generator\JSONSchema\Diff\Schema\Property;
use App\Schema\Generator\JSONSchema\Diff\Schema\PropertyType;
use App\Schema\Generator\JSONSchema\Diff\Schema\Purpose;
use App\Schema\Generator\JSONSchema\Diff\Schema\PurposeSpecification;
use App\Schema\Generator\JSONSchema\Diff\Schema\SubPurpose;
use MinVWS\Codable\Encoder;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;

#[Group('schema-jsonschema-diff')]
class PropertyDiffTest extends UnitTestCase
{
    /**
     * Things we treat as unmodified.
     */
    public static function unmodifiedProvider(): array
    {
        $purposeSpec = new PurposeSpecification(
            [
                new Purpose('purpose1', '', new SubPurpose('subPurpose1', '')),
            ],
            null,
        );

        return [
            'equal-with-purpose-spec' => [
                new Property('name', 'description', PropertyType::type('string'), $purposeSpec),
                new Property('name', 'description', PropertyType::type('string'), $purposeSpec),
            ],
            'equal-without-purpose-spec' => [
                new Property('name', 'description', PropertyType::type('string'), null),
                new Property('name', 'description', PropertyType::type('string'), null),
            ],
            'description-modified' => [
                new Property('name', 'before', PropertyType::type('string'), $purposeSpec),
                new Property('name', 'after', PropertyType::type('string'), $purposeSpec),
            ],
        ];
    }

    #[DataProvider('unmodifiedProvider')]
    public function testDiffShouldReturnNullIfUnmodified(Property $original, Property $new): void
    {
        $this->assertNull($new->diff($original));
        $this->assertNull($original->diff($new));
    }

    public static function modifiedProvider(): array
    {
        $purposeSpec1 = new PurposeSpecification(
            [
                new Purpose('purpose1', '', new SubPurpose('subPurpose1', '')),
            ],
            null,
        );

        $purposeSpec2 = new PurposeSpecification(
            [
                new Purpose('purpose1', '', new SubPurpose('subPurpose2', '')),
            ],
            null,
        );

        return [
            'type-modified' => [
                new Property('name', 'description', PropertyType::type('string'), null),
                new Property('name', 'description', PropertyType::type('int'), null),
                DiffType::Modified,
                null,
            ],
            'purpose-specification-added' => [
                new Property('name', 'description', PropertyType::type('string'), null),
                new Property('name', 'description', PropertyType::type('string'), $purposeSpec1),
                null,
                DiffType::Added,
            ],
            'purpose-specification-removed' => [
                new Property('name', 'description', PropertyType::type('string'), $purposeSpec1),
                new Property('name', 'description', PropertyType::type('string'), null),
                null,
                DiffType::Removed,
            ],
            'purpose-specification-modified' => [
                new Property('name', 'description', PropertyType::type('string'), $purposeSpec1),
                new Property('name', 'description', PropertyType::type('string'), $purposeSpec2),
                null,
                DiffType::Modified,
            ],
            'mixed' => [
                new Property('name', 'description', PropertyType::type('string'), $purposeSpec1),
                new Property('name', 'description', PropertyType::type('int'), $purposeSpec2),
                DiffType::Modified,
                DiffType::Modified,
            ],
        ];
    }

    #[DataProvider('modifiedProvider')]
    public function testDiffShouldReturnModified(Property $original, Property $new, ?DiffType $expectedTypeDiffType, ?DiffType $expectedPurposeSpecificationDiffType): void
    {
        $diff = $new->diff($original);
        $this->assertNotNull($diff);
        $this->assertEquals(DiffType::Modified, $diff->diffType);
        $this->assertSame($new, $diff->new);
        $this->assertSame($original, $diff->original);
        $this->assertEquals($expectedTypeDiffType, $diff->typeDiff?->diffType);
        $this->assertEquals($expectedPurposeSpecificationDiffType, $diff->purposeSpecificationDiff?->diffType);
    }

    public function testEncode(): void
    {
        $purposeSpec = new PurposeSpecification(
            [
                new Purpose('purpose1', '', new SubPurpose('subPurpose1', '')),
            ],
            null,
        );

        $original = new Property('name', 'description', PropertyType::type('int'), null);
        $new = new Property('name', 'description', PropertyType::type('string'), $purposeSpec);

        $diff = $new->diff($original);

        $encoded = (new Encoder())->encode($diff);
        $this->assertEquals('modified', $encoded->diffType);
        $this->assertEquals($diff->original->name, $encoded->name);
        $this->assertEquals($diff->original->description, $encoded->description);
        $this->assertEquals('string', $encoded->type);
        $this->assertEquals('int', $encoded->originalType);
        $this->assertTrue(isset($encoded->purposeSpecificationDiff));
    }
}
