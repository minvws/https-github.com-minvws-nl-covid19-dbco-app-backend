<?php
declare(strict_types=1);

namespace App\Security;

/**
 * Security cache.
 *
 * @package DBCO\HealthAuthorityAPI\Application\Security
 */
interface SecurityCache
{
    public const SK_KEY_EXCHANGE = 'key_exchange';
    public const SK_STORE        = 'store';

    /**
     * Get secret key for the given identifier.
     *
     * @param string $identifier
     *
     * @return string|null
     */
    public function getSecretKey(string $identifier): ?string;
}
