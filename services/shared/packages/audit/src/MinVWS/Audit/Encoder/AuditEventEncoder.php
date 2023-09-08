<?php

declare(strict_types=1);

namespace MinVWS\Audit\Encoder;

use JsonException;
use MinVWS\Audit\DTO\AuditEvent as AuditEventDTO;
use MinVWS\Audit\DTO\AuditObject as AuditObjectDTO;
use MinVWS\Audit\DTO\AuditUser as AuditUserDTO;
use MinVWS\Audit\Models\AuditEvent;
use MinVWS\Audit\Models\AuditObject;
use MinVWS\Audit\Models\AuditUser;
use MinVWS\Codable\JSONEncoder;
use MinVWS\Codable\ValueTypeMismatchException;

final class AuditEventEncoder
{
    /**
     * @throws JsonException
     * @throws ValueTypeMismatchException
     */
    public static function encodeAsJson(AuditEvent $auditEvent): string
    {
        $jsonEncoder = new JSONEncoder();

        $jsonEncoder->getContext()->registerDecorator(AuditEvent::class, new AuditEventDTO());
        $jsonEncoder->getContext()->registerDecorator(AuditObject::class, new AuditObjectDTO());
        $jsonEncoder->getContext()->registerDecorator(AuditUser::class, new AuditUserDTO());

        return $jsonEncoder->encode($auditEvent);
    }
}
