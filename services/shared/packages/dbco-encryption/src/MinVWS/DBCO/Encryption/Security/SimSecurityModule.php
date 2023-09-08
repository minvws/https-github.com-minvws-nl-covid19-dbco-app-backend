<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Encryption\Security;

use RuntimeException;

/**
 * Security module simulator.
 */
class SimSecurityModule implements SecurityModule
{
    private string $path;

    /**
     * Constructor.
     *
     * @param string $path Storage path.
     */
    public function __construct(string $path)
    {
        $this->path = $path;
    }

    private function getSecretKeyPath(string $identifier): string
    {
        return $this->path . '/' . $identifier . '.key';
    }

    /**
     * @inheritdoc
     */
    public function generateSecretKey(string $identifier): string
    {
        $seed = $this->randomBytes(32);
        file_put_contents($this->getSecretKeyPath($identifier), $seed);
        $keypair = sodium_crypto_box_seed_keypair($seed);
        return sodium_crypto_box_secretkey($keypair);
    }

    /**
     * @inheritdoc
     */
    public function hasSecretKey(string $identifier): bool
    {
        return file_exists($this->getSecretKeyPath($identifier));
    }

    /**
     * @inheritdoc
     */
    public function getSecretKey(string $identifier): string
    {
        if (!$this->hasSecretKey($identifier)) {
            throw new RuntimeException("Key with identifier '{$identifier}' does not exist!");
        }

        $seed = file_get_contents($this->getSecretKeyPath($identifier));
        $keypair = sodium_crypto_box_seed_keypair($seed);
        return sodium_crypto_box_secretkey($keypair);
    }

    /**
     * @inheritdoc
     */
    public function deleteSecretKey(string $identifier): void
    {
        unlink($this->getSecretKeyPath($identifier));
    }

    /**
     * @inheritdoc
     */
    public function randomBytes(int $length): string
    {
        return random_bytes($length);
    }

    /**
     * @inheritdoc
     */
    public function listSecretKeys(): array
    {
        return array_map(fn ($path) => basename($path, '.key'), glob($this->path . '/*.key'));
    }
}
