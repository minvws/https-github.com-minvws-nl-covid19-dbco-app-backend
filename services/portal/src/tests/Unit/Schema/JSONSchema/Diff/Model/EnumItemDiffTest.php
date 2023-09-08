<?php

declare(strict_types=1);

namespace Tests\Unit\Schema\JSONSchema\Diff\Model;

use App\Schema\Generator\JSONSchema\Diff\Model\DiffType;
use App\Schema\Generator\JSONSchema\Diff\Model\EnumItemDiff;
use App\Schema\Generator\JSONSchema\Diff\Schema\EnumItem;
use MinVWS\Codable\Encoder;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;

#[Group('schema-jsonschema-diff')]
class EnumItemDiffTest extends UnitTestCase
{
    /**
     * Things we treat as unmodified.
     */
    public static function unmodifiedProvider(): array
    {
        return [
            'equal' => [
                new EnumItem('value', 'description'),
                new EnumItem('value', 'description'),
            ],
            'description-changed' => [
                new EnumItem('value', 'before'),
                new EnumItem('value', 'after'),
            ],
        ];
    }

    #[DataProvider('unmodifiedProvider')]
    public function testDiffShouldReturnNullIfUnmodified(EnumItem $original, EnumItem $new): void
    {
        $this->assertNull($new->diff($original));
        $this->assertNull($original->diff($new));
    }

    public function testDiffShouldReturnModified(): void
    {
        $original = new EnumItem('before', '');
        $new = new EnumItem('after', '');
        $diff = $new->diff($original);
        $this->assertNotNull($diff);
        $this->assertEquals(DiffType::Modified, $diff->diffType);
        $this->assertSame($new, $diff->new);
        $this->assertSame($original, $diff->original);
    }

    public static function encodeProvider(): array
    {
        $item1A = new EnumItem('1', 'a');
        $item1B = new EnumItem('1', 'b');

        return [
            'added' => [
                new EnumItemDiff(DiffType::Added, $item1A, null),
                ['diffType' => 'added', 'const' => $item1A->const, 'description' => $item1A->description],
            ],
            'removed' => [
                new EnumItemDiff(DiffType::Removed, null, $item1A),
                ['diffType' => 'removed', 'const' => $item1A->const, 'description' => $item1A->description],
            ],
            'modified' => [
                new EnumItemDiff(DiffType::Modified, $item1B, $item1A),
                ['diffType' => 'modified', 'const' => $item1B->const, 'description' => $item1B->description],
            ],
        ];
    }

    #[DataProvider('encodeProvider')]
    public function testEncode(EnumItemDiff $diff, array $expectedEncodedData): void
    {
        $encoder = new Encoder();
        $encoder->getContext()->setUseAssociativeArraysForObjects(true);
        $encodedData = $encoder->encode($diff);
        $this->assertEquals($expectedEncodedData, $encodedData);
    }
}
