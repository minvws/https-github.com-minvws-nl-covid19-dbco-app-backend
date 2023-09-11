<?php

declare(strict_types=1);

namespace App\Schema\Generator\JSONSchema\Diff\Schema;

use App\Schema\Generator\JSONSchema\Diff\Model\DiffType;
use App\Schema\Generator\JSONSchema\Diff\Model\PropertyTypeDiff;
use MinVWS\Codable\Encodable;
use MinVWS\Codable\EncodingContainer;

use function assert;

class PropertyType implements Encodable
{
    private function __construct(
        public readonly ?string $ref,
        public readonly ?string $type,
        public readonly ?PropertyType $itemType,
    ) {
    }

    public static function ref(string $ref): self
    {
        return new PropertyType($ref, null, null);
    }

    public static function arr(PropertyType $itemType): self
    {
        return new PropertyType(null, 'array', $itemType);
    }

    public static function type(string $type): self
    {
        return new PropertyType(null, $type, null);
    }

    private function isEqualTo(PropertyType $other): bool
    {
        return (string) $other === (string) $this;
    }

    public function diff(PropertyType $original): ?PropertyTypeDiff
    {
        if ($this->isEqualTo($original)) {
            return null;
        }

        return new PropertyTypeDiff(DiffType::Modified, $this, $original);
    }

    public function __toString(): string
    {
        if ($this->type === 'array') {
            return $this->itemType . '[]';
        }

        if ($this->type !== null) {
            return $this->type;
        }

        assert($this->ref !== null);
        return Descriptor::forRef($this->ref)->id;
    }

    public function encode(EncodingContainer $container): void
    {
        $container->encodeString((string) $this);
    }
}
