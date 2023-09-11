<?php

namespace MinVWS\Metrics\Models\ExportConfig;

/**
 * Signature configuration.
 */
class Signature
{
    public bool $isEnabled;
    public ?string $privateKeyPath;
    public ?string $privateKeyPassphrase;
    public ?string $certPath;

    /**
     * Constructor.
     *
     * @param bool        $isEnabled
     * @param string|null $privateKeyPath
     * @param string|null $privateKeyPassphrase
     * @param string|null $certPath
     */
    public function __construct(
        bool $isEnabled = false,
        ?string $privateKeyPath = null,
        ?string $privateKeyPassphrase = null,
        ?string $certPath = null
    ) {
        $this->isEnabled = $isEnabled;
        $this->privateKeyPath = $privateKeyPath;
        $this->privateKeyPassphrase = $privateKeyPassphrase;
        $this->certPath = $certPath;
    }
}
