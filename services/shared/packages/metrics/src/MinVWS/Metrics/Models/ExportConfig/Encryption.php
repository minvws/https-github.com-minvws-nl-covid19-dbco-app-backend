<?php

namespace MinVWS\Metrics\Models\ExportConfig;

/**
 * Encryption configuration.
 */
class Encryption
{
    public bool $isEnabled;
    public string $cipher;
    public ?string $certPath;

    /**
     * Constructor.
     *
     * @param bool        $isEnabled
     * @param string      $cipher
     * @param string|null $certPath
     */
    public function __construct(
        bool $isEnabled = false,
        string $cipher = 'aes-256-cbc',
        ?string $certPath = null
    ) {
        $this->isEnabled = $isEnabled;
        $this->cipher = $cipher;
        $this->certPath = $certPath;
    }
}
