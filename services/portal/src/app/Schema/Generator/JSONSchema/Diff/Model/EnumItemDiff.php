<?php

declare(strict_types=1);

namespace App\Schema\Generator\JSONSchema\Diff\Model;

use App\Schema\Generator\JSONSchema\Diff\Schema\EnumItem;
use MinVWS\Codable\Encodable;
use MinVWS\Codable\EncodingContainer;

class EnumItemDiff extends Diff implements Encodable
{
    public function __construct(
        DiffType $diffType,
        public readonly ?EnumItem $new,
        public readonly ?EnumItem $original,
    ) {
        parent::__construct($diffType);
    }

    public function encode(EncodingContainer $container): void
    {
        $container->{'diffType'} = $this->diffType;
        $container->{'const'} = ($this->new ?? $this->original)?->const;
        $container->{'description'} = ($this->new ?? $this->original)?->description;
    }
}
