<?php

namespace MinVWS\Audit\DTO;

use MinVWS\Codable\EncodableDecorator;
use MinVWS\Codable\EncodingContainer;
use MinVWS\Audit\Models\AuditUser as AuditUserModel;

/**
 * Default encodable implementation for the audit user model.
 */
class AuditUser implements EncodableDecorator
{
    /**
     * Encode audit user.
     *
     * @param AuditUserModel    $user
     * @param EncodingContainer $container
     */
    public function encode(object $user, EncodingContainer $container): void
    {
        $container->type->encodeString($user->getType());
        $container->identifier->encodeString($user->getIdentifier());
        if ($user->getName() !== null) {
            $container->name->encodeString($user->getName());
        }
        if ($user->getRoles() !== null) {
            $container->roles->encodeArray($user->getRoles());
        }
        if ($user->getPurposes() !== null) {
            $container->purposes->encodeArray($user->getPurposes());
        }
        if ($user->getDetails() !== null) {
            $container->details->encodeArray($user->getDetails());
        }
        if ($user->getIp() !== null) {
            $container->ip->encodeString($user->getIp());
        }
    }
}
