<?php

namespace MinVWS\Audit\DTO;

use MinVWS\Codable\EncodableDecorator;
use MinVWS\Codable\EncodingContainer;
use MinVWS\Audit\Models\AuditEvent as AuditEventModel;

/**
 * Default encodable implementation for the audit event model.
 */
class AuditEvent implements EncodableDecorator
{
    /**
     * Encode audit event.
     *
     * @param AuditEventModel   $event
     * @param EncodingContainer $container
     */
    public function encode(object $event, EncodingContainer $container): void
    {
        $container->type->encodeString('AuditEvent');
        $container->event->code->encodeString($event->getCode());
        $container->event->actionCode->encodeString($event->getActionCode());
        $container->event->description->encodeString($event->getDescription());
        $container->event->createdAt->encodeDateTime($event->getCreatedAt());
        $container->event->result->encodeString($event->getResult());
        $container->users->encodeArray($event->getUsers());
        $container->objects->encodeArray($event->getObjects());
        $container->source->service->encodeString($event->getService());
    }
}
