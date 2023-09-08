<?php

declare(strict_types=1);

namespace Tests\Unit\Schema\JSONSchema\Diff\Model;

use App\Schema\Generator\JSONSchema\Diff\Model\DiffType;
use App\Schema\Generator\JSONSchema\Diff\Schema\Purpose;
use App\Schema\Generator\JSONSchema\Diff\Schema\PurposeSpecification;
use App\Schema\Generator\JSONSchema\Diff\Schema\SubPurpose;
use MinVWS\Codable\Encoder;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;

use function array_keys;
use function count;

#[Group('schema-jsonschema-diff')]
class PurposeSpecificationDiffTest extends UnitTestCase
{
    /**
     * Things we treat as unmodified.
     */
    public static function unmodifiedProvider(): array
    {
        $subPurpose1 = new SubPurpose('subPurpose1', '');
        $purpose1 = new Purpose('purpose1', '', $subPurpose1);
        $purpose2 = new Purpose('purpose2', '', $subPurpose1);

        return [
            'no-purpose' => [
                new PurposeSpecification([], null),
                new PurposeSpecification([], null),
            ],
            'single-purpose' => [
                new PurposeSpecification([$purpose1], null),
                new PurposeSpecification([$purpose1], null),
            ],
            'multiple-purposes' => [
                new PurposeSpecification([$purpose1, $purpose2], null),
                new PurposeSpecification([$purpose1, $purpose2], null),
            ],
            'remark-update-should-be-ignored' => [
                new PurposeSpecification([$purpose1], 'before'),
                new PurposeSpecification([$purpose1], 'after'),
            ],
        ];
    }

    #[DataProvider('unmodifiedProvider')]
    public function testDiffShouldReturnNullIfUnmodified(PurposeSpecification $original, PurposeSpecification $new): void
    {
        $this->assertNull($new->diff($original));
        $this->assertNull($original->diff($new));
    }

    public static function modifiedProvider(): array
    {
        $subPurpose1 = new SubPurpose('subPurpose1', '');
        $subPurpose2 = new SubPurpose('subPurpose2', '');

        $purpose1WithSubPurpose1 = new Purpose('purpose1', '', $subPurpose1);
        $purpose1WithSubPurpose2 = new Purpose('purpose1', '', $subPurpose2);
        $purpose2WithSubPurpose1 = new Purpose('purpose2', '', $subPurpose1);
        $purpose3WithSubPurpose1 = new Purpose('purpose3', '', $subPurpose1);

        return [
            'single-purpose-added' => [
                new PurposeSpecification([], null),
                new PurposeSpecification([$purpose1WithSubPurpose1], null),
                [
                    $purpose1WithSubPurpose1->identifier => DiffType::Added,
                ],
            ],
            'multiple-purposes-added' => [
                new PurposeSpecification([$purpose1WithSubPurpose1], null),
                new PurposeSpecification([$purpose1WithSubPurpose1, $purpose2WithSubPurpose1, $purpose3WithSubPurpose1], null),
                [
                    $purpose2WithSubPurpose1->identifier => DiffType::Added,
                    $purpose3WithSubPurpose1->identifier => DiffType::Added,
                ],
            ],
            'single-purpose-removed' => [
                new PurposeSpecification([$purpose1WithSubPurpose1], null),
                new PurposeSpecification([], null),
                [
                    $purpose1WithSubPurpose1->identifier => DiffType::Removed,
                ],
            ],
            'multiple-purposes-removed' => [
                new PurposeSpecification([$purpose1WithSubPurpose1, $purpose2WithSubPurpose1, $purpose3WithSubPurpose1], null),
                new PurposeSpecification([$purpose1WithSubPurpose1], null),
                [
                    $purpose2WithSubPurpose1->identifier => DiffType::Removed,
                    $purpose3WithSubPurpose1->identifier => DiffType::Removed,
                ],
            ],
            'purpose-modified' => [
                new PurposeSpecification([$purpose1WithSubPurpose1], null),
                new PurposeSpecification([$purpose1WithSubPurpose2], null),
                [
                    $purpose1WithSubPurpose1->identifier => DiffType::Modified,
                ],
            ],
            'mixed' => [
                new PurposeSpecification([$purpose1WithSubPurpose1, $purpose2WithSubPurpose1], null),
                new PurposeSpecification([$purpose1WithSubPurpose2, $purpose3WithSubPurpose1], null),
                [
                    $purpose1WithSubPurpose1->identifier => DiffType::Modified,
                    $purpose2WithSubPurpose1->identifier => DiffType::Removed,
                    $purpose3WithSubPurpose1->identifier => DiffType::Added,
                ],
            ],
        ];
    }

    #[DataProvider('modifiedProvider')]
    public function testDiffShouldReturnModified(PurposeSpecification $original, PurposeSpecification $new, array $expectedPurposeDiffs): void
    {
        $diff = $new->diff($original);
        $this->assertNotNull($diff);
        $this->assertEquals(DiffType::Modified, $diff->diffType);
        $this->assertSame($new, $diff->new);
        $this->assertSame($original, $diff->original);
        $this->assertCount(count($expectedPurposeDiffs), $diff->purposeDiffs);
        foreach ($expectedPurposeDiffs as $identifier => $diffType) {
            $this->assertArrayHasKey($identifier, $diff->purposeDiffs);
            $this->assertEquals($diffType, $diff->purposeDiffs[$identifier]->diffType);
        }
    }

    #[DataProvider('modifiedProvider')]
    public function testEncode(PurposeSpecification $original, PurposeSpecification $new, array $expectedPurposeDiffs): void
    {
        $diff = $new->diff($original);
        $encoded = (new Encoder())->encode($diff);
        $this->assertEquals('modified', $encoded->diffType);
        $this->assertEquals($new->remark ?? $original->remark, $encoded->remark);
        $this->assertEquals(count($expectedPurposeDiffs), count($encoded->purposeDiffs));
        $this->assertEqualsCanonicalizing(array_keys($expectedPurposeDiffs), array_keys($encoded->purposeDiffs));
    }
}
