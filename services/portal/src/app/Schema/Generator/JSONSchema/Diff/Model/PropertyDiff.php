<?php

declare(strict_types=1);

namespace App\Schema\Generator\JSONSchema\Diff\Model;

use App\Schema\Generator\JSONSchema\Diff\Schema\Property;
use MinVWS\Codable\Encodable;
use MinVWS\Codable\EncodingContainer;

class PropertyDiff extends Diff implements Encodable
{
    public function __construct(
        DiffType $diffType,
        public readonly ?Property $new,
        public readonly ?Property $original,
        public readonly ?PropertyTypeDiff $typeDiff,
        public readonly ?PurposeSpecificationDiff $purposeSpecificationDiff,
    ) {
        parent::__construct($diffType);
    }

    public function encode(EncodingContainer $container): void
    {
        $container->{'diffType'} = $this->diffType;

        $container->{'name'} = ($this->new ?? $this->original)?->name;
        $container->{'description'} = ($this->new ?? $this->original)?->description;

        if (isset($this->typeDiff->new)) {
            $container->{'type'} = $this->typeDiff->new;
        }

        if (isset($this->typeDiff->original)) {
            $container->{'originalType'} = $this->typeDiff->original;
        }

        if (isset($this->purposeSpecificationDiff)) {
            $container->{'purposeSpecificationDiff'} = $this->purposeSpecificationDiff;
        }
    }
}
