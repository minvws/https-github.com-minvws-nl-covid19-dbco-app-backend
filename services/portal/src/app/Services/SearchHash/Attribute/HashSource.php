<?php

declare(strict_types=1);

namespace App\Services\SearchHash\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class HashSource
{
    public function __construct(public string $source)
    {
    }
}
