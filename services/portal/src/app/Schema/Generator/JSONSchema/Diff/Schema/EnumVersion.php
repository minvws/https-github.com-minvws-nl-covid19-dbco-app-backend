<?php

declare(strict_types=1);

namespace App\Schema\Generator\JSONSchema\Diff\Schema;

use App\Schema\Generator\JSONSchema\Diff\Model\DiffList;
use App\Schema\Generator\JSONSchema\Diff\Model\DiffType;
use App\Schema\Generator\JSONSchema\Diff\Model\EnumItemDiff;
use App\Schema\Generator\JSONSchema\Diff\Model\EnumVersionDiff;
use MinVWS\Codable\Decodable;
use MinVWS\Codable\DecodingContainer;

use function array_combine;
use function array_keys;
use function array_map;
use function array_merge;
use function array_unique;
use function ksort;

use const SORT_STRING;

class EnumVersion implements Decodable
{
    public readonly string $id;
    public readonly string $name;
    public readonly int $version;

    /** @var array<string, EnumItem> */
    public readonly array $items;

    /**
     * @param array<EnumItem> $items
     */
    public function __construct(
        public readonly Descriptor $descriptor,
        public readonly ?string $title,
        public readonly ?string $description,
        array $items,
    ) {
        $this->id = $descriptor->id;
        $this->name = $descriptor->name;
        $this->version = $descriptor->version;

        $indexedItems = array_combine(array_map(static fn ($i) => $i->const, $items), $items);
        ksort($indexedItems, SORT_STRING);
        $this->items = $indexedItems;
    }

    public static function decode(DecodingContainer $container, ?object $object = null): self
    {
        $descriptor = !$container->contains('$id') && $container->getKey() !== null
            ? Descriptor::forEnumVersionDefKey((string) $container->getKey())
            : Descriptor::forEnumVersionId(
                $container->{'$id'}->decodeString(),
            );

        $title = $container->{'title'}->decodeStringIfPresent();
        $description = $container->{'description'}->decodeStringIfPresent();
        $items = $container->{'oneOf'}->decodeArray(EnumItem::class);
        return new self($descriptor, $title, $description, $items);
    }

    /**
     * @return array<string>
     */
    private function getItemConsts(): array
    {
        // php changes numeric string keys automatically to integers
        return array_map(static fn ($i) => (string) $i, array_keys($this->items));
    }

    private function getItem(string $const): ?EnumItem
    {
        return $this->items[$const] ?? null;
    }

    public function diff(EnumVersion $original): ?EnumVersionDiff
    {
        /** @var array<string> $itemConsts */
        $itemConsts = array_unique(array_merge($this->getItemConsts(), $original->getItemConsts()));

        /** @var DiffList<string, EnumItemDiff> $itemDiffs */
        $itemDiffs = new DiffList();
        foreach ($itemConsts as $itemConst) {
            $newItem = $this->getItem($itemConst);
            $originalItem = $original->getItem($itemConst);

            if (isset($newItem) && isset($originalItem)) {
                $diff = $newItem->diff($originalItem);
                if ($diff !== null) {
                    $itemDiffs[$itemConst] = $diff;
                }
            } elseif (isset($newItem)) {
                $itemDiffs[$itemConst] = new EnumItemDiff(DiffType::Added, $newItem, null);
            } elseif (isset($originalItem)) {
                $itemDiffs[$itemConst] = new EnumItemDiff(DiffType::Removed, null, $originalItem);
            }
        }

        if ($itemDiffs->isEmpty()) {
            return null;
        }

        return new EnumVersionDiff(DiffType::Modified, $this, $original, $itemDiffs);
    }
}
