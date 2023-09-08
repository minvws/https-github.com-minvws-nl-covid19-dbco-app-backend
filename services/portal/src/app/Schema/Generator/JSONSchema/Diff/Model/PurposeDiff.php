<?php

declare(strict_types=1);

namespace App\Schema\Generator\JSONSchema\Diff\Model;

use App\Schema\Generator\JSONSchema\Diff\Schema\Purpose;
use MinVWS\Codable\Encodable;
use MinVWS\Codable\EncodingContainer;

class PurposeDiff extends Diff implements Encodable
{
    public function __construct(
        DiffType $diffType,
        public readonly ?Purpose $new,
        public readonly ?Purpose $original,
    ) {
        parent::__construct($diffType);
    }

    public function encode(EncodingContainer $container): void
    {
        $container->{'diffType'} = $this->diffType;

        $container->{'identifier'} = ($this->new ?? $this->original)?->identifier;
        $container->{'description'} = ($this->new ?? $this->original)?->description;

        if (isset($this->new)) {
            $container->{'subPurpose'}->{'identifier'} = $this->new->subPurpose->identifier;
            $container->{'subPurpose'}->{'description'} = $this->new->subPurpose->description;
        }

        if (!isset($this->original)) {
            return;
        }

        $container->{'originalSubPurpose'}->{'identifier'} = $this->original->subPurpose->identifier;
        $container->{'originalSubPurpose'}->{'description'} = $this->original->subPurpose->description;
    }
}
