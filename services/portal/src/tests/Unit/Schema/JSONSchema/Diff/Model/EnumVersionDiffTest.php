<?php

declare(strict_types=1);

namespace Tests\Unit\Schema\JSONSchema\Diff\Model;

use App\Schema\Generator\JSONSchema\Diff\Model\DiffType;
use App\Schema\Generator\JSONSchema\Diff\Schema\Descriptor;
use App\Schema\Generator\JSONSchema\Diff\Schema\EnumItem;
use App\Schema\Generator\JSONSchema\Diff\Schema\EnumVersion;
use MinVWS\Codable\Encoder;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;

use function count;
use function strtolower;

#[Group('schema-jsonschema-diff')]
class EnumVersionDiffTest extends UnitTestCase
{
    public static function unmodifiedProvider(): array
    {
        $desc = Descriptor::forEnumVersionId('/schemas/enums/YesNoUnknown/V1');

        $item1 = new EnumItem('item1', '');
        $item2 = new EnumItem('item2', '');

        return [
            'equal' => [
                new EnumVersion($desc, 'title', 'description', []),
                new EnumVersion($desc, 'title', 'description', []),
            ],
            'equal-with-items' => [
                new EnumVersion($desc, 'title', 'description', [$item1, $item2]),
                new EnumVersion($desc, 'title', 'description', [$item1, $item2]),
            ],
            'title-modified' => [
                new EnumVersion($desc, 'before', 'description', [$item1]),
                new EnumVersion($desc, 'after', 'description', [$item1]),
            ],
            'description-modified' => [
                new EnumVersion($desc, 'title', 'before', [$item1]),
                new EnumVersion($desc, 'title', 'after', [$item1]),
            ],
        ];
    }

    #[DataProvider('unmodifiedProvider')]
    public function testDiffShouldReturnNullIfUnmodified(EnumVersion $original, EnumVersion $new): void
    {
        $this->assertNull($new->diff($original));
        $this->assertNull($original->diff($new));
    }

    public static function modifiedProvider(): array
    {
        $desc = Descriptor::forEnumVersionId('/schemas/json/schemas/enums/YesNoUnknown/V1');

        $item1 = new EnumItem('item1', '');
        $item2 = new EnumItem('item2', '');

        return [
            'item-added' => [
                new EnumVersion($desc, 'title', 'description', []),
                new EnumVersion($desc, 'title', 'description', [$item1]),
                [
                    $item1->const => DiffType::Added,
                ],
            ],
            'items-added' => [
                new EnumVersion($desc, 'title', 'description', []),
                new EnumVersion($desc, 'title', 'description', [$item1, $item2]),
                [
                    $item1->const => DiffType::Added,
                    $item2->const => DiffType::Added,
                ],
            ],
            'item-removed' => [
                new EnumVersion($desc, 'title', 'description', [$item1]),
                new EnumVersion($desc, 'title', 'description', []),
                [
                    $item1->const => DiffType::Removed,
                ],
            ],
            'items-removed' => [
                new EnumVersion($desc, 'title', 'description', [$item1, $item2]),
                new EnumVersion($desc, 'title', 'description', []),
                [
                    $item1->const => DiffType::Removed,
                    $item2->const => DiffType::Removed,
                ],
            ],
        ];
    }

    #[DataProvider('modifiedProvider')]
    public function testDiffShouldReturnModified(EnumVersion $original, EnumVersion $new, array $expectedItemDiffs): void
    {
        $diff = $new->diff($original);
        $this->assertNotNull($diff);
        $this->assertEquals(DiffType::Modified, $diff->diffType);
        $this->assertSame($new, $diff->new);
        $this->assertSame($original, $diff->original);
        $this->assertCount(count($expectedItemDiffs), $diff->itemDiffs);
        foreach ($expectedItemDiffs as $const => $diffType) {
            $this->assertArrayHasKey($const, $diff->itemDiffs);
            $this->assertEquals($diffType, $diff->itemDiffs[$const]->diffType);
        }
    }

    #[DataProvider('modifiedProvider')]
    public function testEncode(EnumVersion $original, EnumVersion $new, array $expectedItemDiffs): void
    {
        $diff = $new->diff($original);
        $encoder = new Encoder();
        $encoder->getContext()->setUseAssociativeArraysForObjects(true);
        $encodedData = $encoder->encode($diff);

        $this->assertEquals($new->id, $encodedData['id']);
        $this->assertEquals('enum', $encodedData['type']);
        $this->assertEquals($new->name, $encodedData['name']);
        $this->assertEquals($new->version, $encodedData['version']);

        $this->assertCount(count($expectedItemDiffs), $encodedData['itemDiffs']);
        foreach ($expectedItemDiffs as $const => $diffType) {
            $this->assertArrayHasKey($const, $encodedData['itemDiffs']);
            $this->assertEquals($const, $encodedData['itemDiffs'][$const]['const']);
            $this->assertEquals(strtolower($diffType->name), $encodedData['itemDiffs'][$const]['diffType']);
        }
    }
}
