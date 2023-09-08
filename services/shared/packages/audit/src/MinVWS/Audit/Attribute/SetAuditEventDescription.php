<?php

declare(strict_types=1);

namespace MinVWS\Audit\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final class SetAuditEventDescription
{
    public function __construct(public string $description)
    {
    }
}
