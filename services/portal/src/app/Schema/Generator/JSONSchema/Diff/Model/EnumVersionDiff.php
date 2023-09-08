<?php

declare(strict_types=1);

namespace App\Schema\Generator\JSONSchema\Diff\Model;

use App\Schema\Generator\JSONSchema\Diff\Schema\EnumVersion;
use MinVWS\Codable\Encodable;
use MinVWS\Codable\EncodingContainer;

class EnumVersionDiff extends Diff implements Encodable
{
    /**
     * @param DiffList<string, EnumItemDiff>|null $itemDiffs
     */
    public function __construct(
        DiffType $diffType,
        public readonly ?EnumVersion $new,
        public readonly ?EnumVersion $original,
        public readonly ?DiffList $itemDiffs = null,
    ) {
        parent::__construct($diffType);
    }

    public function encode(EncodingContainer $container): void
    {
        $container->{'diffType'} = $this->diffType;

        $container->{'id'} = ($this->new ?? $this->original)?->id;
        $container->{'type'} = 'enum';
        $container->{'name'} = ($this->new ?? $this->original)?->name;
        $container->{'version'} = ($this->new ?? $this->original)?->version;

        if (isset($this->itemDiffs)) {
            $container->{'itemDiffs'} = $this->itemDiffs;
        }
    }
}
