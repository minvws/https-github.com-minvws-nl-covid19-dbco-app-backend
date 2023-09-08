<?php

namespace MinVWS\HealthCheck\Models;

/**
 * Result of health check.
 */
class HealthCheckResult implements \JsonSerializable
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
    public function __construct(bool $isHealthy, ?string $errorCode = null, ?string $errorMessage = null)
    {
        $this->isHealthy = $isHealthy;
        $this->errorCode = $errorCode;
        $this->errorMessage = $errorMessage;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array
    {
        $data = ['isHealthy' => $this->isHealthy];

        if (!empty($this->errorCode) && !empty($this->errorMessage)) {
            $data['errorCode'] = $this->errorCode;
            $data['errorMessage'] = $this->errorMessage;
        }

        return $data;
    }
}
