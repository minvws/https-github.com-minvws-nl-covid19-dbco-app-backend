<?php
namespace MinVWS\HealthCheck\Models;

/**
 * Result of health check.
 */
class HealthCheckResult
{
    /**
     * @var bool
     */
    public bool $isHealthy;

    /**
     * @var string|null
     */
    public ?string $errorCode;

    /**
     * @var string|null
     */
    public ?string $errorMessage;

    /**
     * HealthCheckResult constructor.
     *
     * @param bool        $isHealthy
     * @param string|null $errorCode
     * @param string|null $errorMessage
     */
    public function __construct(bool $isHealthy, ?string $errorCode, ?string $errorMessage)
    {
        $this->isHealthy = $isHealthy;
        $this->errorCode = $errorCode;
        $this->errorMessage = $errorMessage;
    }
}