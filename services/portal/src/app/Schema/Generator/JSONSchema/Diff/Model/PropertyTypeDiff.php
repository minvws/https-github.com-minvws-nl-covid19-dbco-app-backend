<?php

declare(strict_types=1);

namespace App\Schema\Generator\JSONSchema\Diff\Model;

use App\Schema\Generator\JSONSchema\Diff\Schema\PropertyType;

class PropertyTypeDiff extends Diff
{
    public function __construct(
        DiffType $diffType,
        public readonly ?PropertyType $new,
        public readonly ?PropertyType $original,
    ) {
        parent::__construct($diffType);
    }
}
