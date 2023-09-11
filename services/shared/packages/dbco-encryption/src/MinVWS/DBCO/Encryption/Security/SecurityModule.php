<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Encryption\Security;

interface SecurityModule
{
    public const SK_KEY_EXCHANGE  = 'key_exchange';
    public const SK_PUBLIC_PORTAL = 'public_portal';
    public const SK_TEST_RESULT   = 'test_result';
    public const SK_EXPORT_CLIENT = 'export_client';

    public const SK_DEFAULT_KEYS  = [self::SK_KEY_EXCHANGE, self::SK_PUBLIC_PORTAL, self::SK_TEST_RESULT, self::SK_EXPORT_CLIENT];

    public const SK_STORE_LEGACY   = 'store';
    public const SK_STORE_TEMPLATE = 'store:%s';

    /**
     * Generate / store secret key.
     *
     * @param string $identifier
     *
     * @return string
     */
    public function generateSecretKey(string $identifier): string;

    /**
     * Checks if the secret key exists.
     *
     * @param string $identifier
     *
     * @return bool
     */
    public function hasSecretKey(string $identifier): bool;

    /**
     * Get secret key for the given identifier.
     *
     * @param string $identifier
     *
     * @return string
     */
    public function getSecretKey(string $identifier): string;

    /**
     * Delete secret key.
     *
     * @param string $identifier
     */
    public function deleteSecretKey(string $identifier): void;

    /**
     * Lists all secret keys.
     *
     * @return string[] List of key identifiers.
     */
    public function listSecretKeys(): array;

    /**
     * Get random bytes.
     *
     * @param int $length
     *
     * @return string
     */
    public function randomBytes(int $length): string;
}
