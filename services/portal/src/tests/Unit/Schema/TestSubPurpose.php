<?php

/** @noinspection PhpHierarchyChecksInspection */

declare(strict_types=1);

namespace Tests\Unit\Schema;

use App\Schema\Purpose\SubPurpose;

enum TestSubPurpose: string implements SubPurpose
{
    case SubPurposeA = 'a';
    case SubPurposeB = 'b';
    case SubPurposeC = 'c';

    public function getIdentifier(): string
    {
        return $this->value;
    }

    public function getLabel(): string
    {
        return $this->name;
    }
}
