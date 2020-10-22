<?php
declare(strict_types=1);

namespace DBCO\PrivateAPI\Application\Helpers;

/**
 * Utility methods for helping with the JWT configuration.
 *
 * @package DBCO\PrivateAPI\Application\Helpers
 */
class JWTConfigHelper
{
    /**
     * @var bool
     */
    private $isEnabled;

    /**
     * @var array
     */
    private array $config;

    /**
     * Constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->isEnabled = $config['enabled'] ?? true;
        unset($config['enabled']);
        $this->config = $config;
    }

    /**
     * Returns if JWT Authorization is enabled.
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }

    /**
     * Returns the config data.
     *
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }
}
