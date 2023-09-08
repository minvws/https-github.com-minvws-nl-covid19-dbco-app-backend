<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\Attributes;

use Attribute;
use BackedEnum;
use MinVWS\DBCO\Enum\Models\Enum;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class RequestHasFixedValuesQueryFilter
{
    /**
     * @param class-string<BackedEnum|Enum> $enumClass
     */
    public function __construct(
        public readonly string $name,
        public readonly string $enumClass,
        public readonly bool $required = false,
    )
    {
    }
}
