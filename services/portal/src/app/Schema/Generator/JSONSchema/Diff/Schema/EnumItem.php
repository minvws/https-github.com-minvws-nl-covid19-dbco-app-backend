<?php

declare(strict_types=1);

namespace App\Schema\Generator\JSONSchema\Diff\Schema;

use App\Schema\Generator\JSONSchema\Diff\Model\DiffType;
use App\Schema\Generator\JSONSchema\Diff\Model\EnumItemDiff;
use MinVWS\Codable\Decodable;
use MinVWS\Codable\DecodingContainer;

class EnumItem implements Decodable
{
    public function __construct(public string $const, public string $description)
    {
    }

    public static function decode(DecodingContainer $container, ?object $object = null): self
    {
        $const = $container->{'const'}->decodeString();
        $description = $container->{'description'}->decodeString();
        return new self($const, $description);
    }

    public function diff(EnumItem $original): ?EnumItemDiff
    {
        if ($this->const === $original->const) {
            return null;
        }

        return new EnumItemDiff(DiffType::Modified, $this, $original);
    }
}
