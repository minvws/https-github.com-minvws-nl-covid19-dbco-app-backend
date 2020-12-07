<?php
declare(strict_types=1);

namespace DBCO\HealthAuthorityAPI\Application\Security;

use RuntimeException;

/**
 * Hardware security module.
 *
 * @package DBCO\HealthAuthorityAPI\Application\Security
 */
class HSMSecurityModule implements SecurityModule
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        throw new RuntimeException('Not implemented!'); // TODO
    }

    /**
     * @inheritdoc
     */
    public function generateSecretKey(string $identifier): string
    {
        throw new RuntimeException('Not implemented!'); // TODO
    }

    /**
     * @inheritdoc
     */
    public function getSecretKey(string $identifier): string
    {
        throw new RuntimeException('Not implemented!'); // TODO
    }

    /**
     * @inheritdoc
     */
    public function deleteSecretKey(string $identifier): void
    {
        throw new RuntimeException('Not implemented!'); // TODO
    }

    /**
     * @inheritdoc
     */
    public function renameSecretKey(string $oldIdentifier, string $newIdentifier)
    {
        throw new RuntimeException('Not implemented!'); // TODO
    }

    /**
     * @inheritdoc
     */
    public function randomBytes(int $length): string
    {
        throw new RuntimeException('Not implemented!'); // TODO
    }
}
