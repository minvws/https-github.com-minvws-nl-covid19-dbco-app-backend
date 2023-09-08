<?php

namespace MinVWS\Audit\DTO;

use MinVWS\Codable\EncodableDecorator;
use MinVWS\Codable\EncodingContainer;
use MinVWS\Audit\Models\AuditObject as AuditObjectModel;

/**
 * Default encodable implementation for the audit object model.
 */
class AuditObject implements EncodableDecorator
{
    /**
     * Encode audit event.
     *
     * @param AuditObjectModel  $object
     * @param EncodingContainer $container
     */
    public function encode(object $object, EncodingContainer $container): void
    {
        $container->type->encodeString($object->getType());
        $container->identifier->encodeString($object->getIdentifier());
        if ($object->getDetails() !== null) {
            $container->details->encodeArray($object->getDetails());
        }
    }
}
