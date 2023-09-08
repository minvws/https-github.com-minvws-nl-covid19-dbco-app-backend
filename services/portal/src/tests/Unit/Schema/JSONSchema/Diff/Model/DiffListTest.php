<?php

declare(strict_types=1);

namespace Tests\Unit\Schema\JSONSchema\Diff\Model;

use App\Schema\Generator\JSONSchema\Diff\Model\DiffList;
use App\Schema\Generator\JSONSchema\Diff\Model\DiffType;
use App\Schema\Generator\JSONSchema\Diff\Model\EnumItemDiff;
use App\Schema\Generator\JSONSchema\Diff\Schema\EnumItem;
use MinVWS\Codable\Encoder;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;

use function array_keys;
use function collect;

#[Group('schema-jsonschema-diff')]
class DiffListTest extends UnitTestCase
{
    public function testMutations(): void
    {
        $list = new DiffList();
        $this->assertEquals(0, $list->count());
        $this->assertTrue($list->isEmpty());

        $list->set('c', new EnumItemDiff(DiffType::Modified, new EnumItem('c', 'C2'), new EnumItem('c', 'C1')));
        $list->set('a', new EnumItemDiff(DiffType::Added, new EnumItem('b', 'B'), null));
        $list->set('b', new EnumItemDiff(DiffType::Removed, null, new EnumItem('a', 'A')));
        $this->assertCount(3, $list);
        $this->assertTrue($list->exists('a'));
        $this->assertNotNull($list->get('b'));
        $this->assertEquals(DiffType::Modified, $list->get('c')?->diffType);

        $keys = collect($list)->keys()->toArray();
        $this->assertEquals(['c', 'a', 'b'], $keys);

        $list->unset('a');
        $this->assertCount(2, $list);
        $this->assertNull($list['a']);
    }

    public function testArrayAccess(): void
    {
        $list = new DiffList();

        $this->assertFalse(isset($list['a']));
        $this->assertFalse($list->exists('a'));
        $this->assertNull($list['a']);

        $list['a'] = new EnumItemDiff(DiffType::Added, new EnumItem('a', 'A'), null);
        $this->assertTrue(isset($list['a']));
        $this->assertTrue($list->exists('a'));
        $this->assertNotNull($list['a']);
        $this->assertEquals('A', $list['a']?->new?->description);

        unset($list['a']);
        $this->assertFalse(isset($list['a']));
        $this->assertFalse($list->exists('a'));
        $this->assertNull($list['a']);
    }

    public function testFilter(): void
    {
        $list = new DiffList();
        $list->set('a', new EnumItemDiff(DiffType::Added, new EnumItem('a', 'A'), null));
        $list->set('b', new EnumItemDiff(DiffType::Added, new EnumItem('b', 'B'), null));
        $list->set('c', new EnumItemDiff(DiffType::Modified, new EnumItem('c', 'C2'), new EnumItem('c', 'C1')));
        $list->set('d', new EnumItemDiff(DiffType::Removed, null, new EnumItem('d', 'D')));

        $this->assertCount(2, $list->filter(DiffType::Added));
        $this->assertCount(3, $list->filter(DiffType::Added, DiffType::Removed));
        $this->assertCount(1, $list->filter(DiffType::Modified));
    }

    public function testEncode(): void
    {
        $list = new DiffList();
        $list->set('a', new EnumItemDiff(DiffType::Added, new EnumItem('a', 'A'), null));
        $list->set('b', new EnumItemDiff(DiffType::Added, new EnumItem('a', 'A'), null));
        $encoded = (new Encoder())->encode($list);
        $this->assertIsArray($encoded);
        $this->assertCount(2, $encoded);
        $this->assertEquals(['a', 'b'], array_keys($encoded));
    }
}
