<?php

declare(strict_types=1);

namespace App\Schema\Generator\JSONSchema\Diff\Model;

use App\Schema\Generator\JSONSchema\Diff\Schema\SchemaList;
use MinVWS\Codable\Encodable;
use MinVWS\Codable\EncodingContainer;

class SchemaListDiff extends Diff implements Encodable
{
    /**
     * @param DiffList<string, SchemaDiff>|null $schemaDiffs
     */
    public function __construct(
        DiffType $diffType,
        public readonly ?SchemaList $new,
        public readonly ?SchemaList $original,
        public readonly ?DiffList $schemaDiffs,
    ) {
        parent::__construct($diffType);
    }

    public function encode(EncodingContainer $container): void
    {
        $container->{'diffType'} = $this->diffType;

        if (isset($this->schemaDiffs)) {
            $container->{'schemaDiffs'} = $this->schemaDiffs;
        }
    }
}
