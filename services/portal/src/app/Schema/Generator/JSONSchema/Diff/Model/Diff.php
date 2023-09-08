<?php

declare(strict_types=1);

namespace App\Schema\Generator\JSONSchema\Diff\Model;

abstract class Diff
{
    public function __construct(public readonly DiffType $diffType)
    {
    }
}
