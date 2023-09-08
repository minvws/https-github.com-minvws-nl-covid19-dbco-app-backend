<?php

declare(strict_types=1);

namespace App\Schema\Generator\JSONSchema\Diff\Model;

use App\Schema\Generator\JSONSchema\Diff\Schema\SchemaVersion;
use MinVWS\Codable\Encodable;
use MinVWS\Codable\EncodingContainer;

class SchemaVersionDiff extends Diff implements Encodable
{
    /**
     * @param DiffList<string, PropertyDiff>|null $propertyDiffs
     * @param DiffList<string, SchemaVersionDiff|EnumVersionDiff>|null $defDiffs
     */
    public function __construct(
        DiffType $diffType,
        public readonly ?SchemaVersion $new,
        public readonly ?SchemaVersion $original,
        public readonly ?DiffList $propertyDiffs,
        public readonly ?DiffList $defDiffs,
    ) {
        parent::__construct($diffType);
    }

    public function encode(EncodingContainer $container): void
    {
        $container->{'diffType'} = $this->diffType;

        $container->{'id'} = ($this->new ?? $this->original)?->id;
        $container->{'type'} = 'object';
        $container->{'name'} = ($this->new ?? $this->original)?->name;
        $container->{'version'} = ($this->new ?? $this->original)?->version;
        $container->{'description'} = ($this->new ?? $this->original)?->description;

        if (isset($this->propertyDiffs)) {
            $container->{'propertyDiffs'} = $this->propertyDiffs;
        }

        if (isset($this->defDiffs)) {
            $container->{'defDiffs'} = $this->defDiffs;
        }
    }
}
