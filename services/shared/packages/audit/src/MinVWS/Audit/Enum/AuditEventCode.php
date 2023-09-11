<?php

declare(strict_types=1);

namespace MinVWS\Audit\Enum;

enum AuditEventCode: string
{
    case CREATE = 'C';
    case READ = 'R';
    case UPDATE = 'U';
    case DELETE = 'D';
    case EXECUTE = 'E';
}
