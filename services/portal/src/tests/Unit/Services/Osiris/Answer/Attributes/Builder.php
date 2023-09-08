<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Osiris\Answer\Attributes;

use App\Services\Osiris\Answer;
use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Builder
{
    /**
     * @param class-string<Answer\Builder> $class
     */
    public function __construct(public readonly string $class)
    {
    }
}
