<?php

/** @noinspection PhpHierarchyChecksInspection */

declare(strict_types=1);

namespace Tests\Unit\Schema;

use App\Schema\Purpose\Purpose;

/**
 * @implements Purpose<TestSubPurpose>
 */
enum TestPurpose: string implements Purpose
{
    case PurposeA = 'a';
    case PurposeB = 'b';
    case PurposeC = 'c';

    public function getIdentifier(): string
    {
        return $this->value;
    }

    public function getLabel(): string
    {
        return $this->name;
    }
}
