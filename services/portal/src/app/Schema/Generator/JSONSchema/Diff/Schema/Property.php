<?php

declare(strict_types=1);

namespace App\Schema\Generator\JSONSchema\Diff\Schema;

use App\Schema\Generator\JSONSchema\Diff\Model\DiffType;
use App\Schema\Generator\JSONSchema\Diff\Model\PropertyDiff;
use App\Schema\Generator\JSONSchema\Diff\Model\PurposeSpecificationDiff;
use MinVWS\Codable\Decodable;
use MinVWS\Codable\DecodingContainer;

use function assert;
use function is_string;

class Property implements Decodable
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $description,
        public readonly PropertyType $type,
        public readonly ?PurposeSpecification $purposeSpecification,
    ) {
    }

    private static function decodePropertyType(DecodingContainer $container): PropertyType
    {
        $ref = $container->{'$ref'}->decodeStringIfPresent();
        if ($ref !== null) {
            return PropertyType::ref($ref);
        }

        $type = $container->{'type'}->decodeStringIfPresent() ?? 'any';
        if ($type === 'array') {
            $itemType = self::decodePropertyType($container->items);
            return PropertyType::arr($itemType);
        }

        return PropertyType::type($type);
    }

    public static function decode(DecodingContainer $container, ?object $object = null): self
    {
        $name = $container->getKey();
        assert(is_string($name));
        $description = $container->{'description'}->decodeStringIfPresent();
        $type = self::decodePropertyType($container);
        $purposeSpecification = $container->{'purposeSpecification'}->decodeObjectIfPresent(PurposeSpecification::class);
        return new self($name, $description, $type, $purposeSpecification);
    }

    public function diff(Property $original): ?PropertyDiff
    {
        $typeDiff = $this->type->diff($original->type);

        $purposeSpecificationDiff = null;
        if (isset($this->purposeSpecification) && isset($original->purposeSpecification)) {
            $purposeSpecificationDiff = $this->purposeSpecification->diff($original->purposeSpecification);
        } elseif (isset($this->purposeSpecification)) {
            $purposeSpecificationDiff = new PurposeSpecificationDiff(DiffType::Added, $this->purposeSpecification, null, null);
        } elseif (isset($original->purposeSpecification)) {
            $purposeSpecificationDiff = new PurposeSpecificationDiff(DiffType::Removed, null, $original->purposeSpecification, null);
        }

        if ($typeDiff === null && $purposeSpecificationDiff === null) {
            return null;
        }

        return new PropertyDiff(DiffType::Modified, $this, $original, $typeDiff, $purposeSpecificationDiff);
    }
}
