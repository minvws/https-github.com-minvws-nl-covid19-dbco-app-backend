<?php

declare(strict_types=1);

namespace MinVWS\Audit\Attribute;

use Attribute;
use MinVWS\Audit\Enum\AuditEventCode;

#[Attribute(Attribute::TARGET_METHOD)]
class SetAuditEventCode
{
    public readonly string $code;

    public function __construct(AuditEventCode $code)
    {
        $this->code = $code->value;
    }
}
