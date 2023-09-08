<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class RequestQueryFilter
{
    public function __construct(public readonly string $name)
    {
    }
}
