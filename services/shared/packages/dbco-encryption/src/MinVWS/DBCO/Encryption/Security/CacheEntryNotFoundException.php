<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Encryption\Security;

/**
 * Thrown when a certain key / identifier doesn't exist in the security cache.
 */
class CacheEntryNotFoundException extends EncryptionException
{
}
