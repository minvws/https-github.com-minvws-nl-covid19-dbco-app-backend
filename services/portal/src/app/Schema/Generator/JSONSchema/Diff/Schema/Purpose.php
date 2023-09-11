<?php

declare(strict_types=1);

namespace App\Schema\Generator\JSONSchema\Diff\Schema;

use App\Schema\Generator\JSONSchema\Diff\Model\DiffType;
use App\Schema\Generator\JSONSchema\Diff\Model\PurposeDiff;
use MinVWS\Codable\Decodable;
use MinVWS\Codable\DecodingContainer;

use function assert;
use function is_string;

class Purpose implements Decodable
{
    public function __construct(public readonly string $identifier, public readonly string $description, public readonly SubPurpose $subPurpose)
    {
    }

    public static function decode(DecodingContainer $container, ?object $object = null): self
    {
        $identifier = $container->getKey();
        assert(is_string($identifier));
        $description = $container->{'description'}->decodeString();
        $subPurpose = $container->{'subPurpose'}->decodeObject(SubPurpose::class);
        return new self($identifier, $description, $subPurpose);
    }

    public function diff(Purpose $original): ?PurposeDiff
    {
        if ($this->subPurpose->identifier === $original->subPurpose->identifier) {
            return null;
        }

        return new PurposeDiff(DiffType::Modified, $this, $original);
    }
}
