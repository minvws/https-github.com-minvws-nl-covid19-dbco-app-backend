<?php

declare(strict_types=1);

namespace App\Schema\Generator\JSONSchema\Diff\Model;

use App\Schema\Generator\JSONSchema\Diff\Schema\PurposeSpecification;
use MinVWS\Codable\Encodable;
use MinVWS\Codable\EncodingContainer;

class PurposeSpecificationDiff extends Diff implements Encodable
{
    /**
     * @param DiffList<string, PurposeDiff>|null $purposeDiffs
     */
    public function __construct(
        DiffType $diffType,
        public readonly ?PurposeSpecification $new,
        public readonly ?PurposeSpecification $original,
        public readonly ?DiffList $purposeDiffs,
    ) {
        parent::__construct($diffType);
    }

    public function encode(EncodingContainer $container): void
    {
        $container->{'diffType'} = $this->diffType;

        $container->{'remark'} = $this->new->remark ?? $this->original->remark ?? null;

        if (isset($this->purposeDiffs)) {
            $container->{'purposeDiffs'} = $this->purposeDiffs;
        }
    }
}
