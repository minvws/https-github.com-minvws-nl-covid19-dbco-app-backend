<?php

declare(strict_types=1);

namespace App\Schema\Generator\JSONSchema\Diff\Model;

use App\Schema\Generator\JSONSchema\Diff\Schema\Schema;
use MinVWS\Codable\Encodable;
use MinVWS\Codable\EncodingContainer;

class SchemaDiff extends Diff implements Encodable
{
    /**
     * @param DiffList<int, SchemaVersionDiff>|null $versionDiffs
     */
    public function __construct(
        DiffType $diffType,
        public readonly ?Schema $new,
        public readonly ?Schema $original,
        public readonly ?DiffList $versionDiffs,
    ) {
        parent::__construct($diffType);
    }

    public function encode(EncodingContainer $container): void
    {
        $container->{'diffType'} = $this->diffType;
        $container->{'name'} = ($this->new ?? $this->original)?->name;

        if (isset($this->versionDiffs)) {
            $container->{'versionDiffs'} = $this->versionDiffs;
        }
    }
}
