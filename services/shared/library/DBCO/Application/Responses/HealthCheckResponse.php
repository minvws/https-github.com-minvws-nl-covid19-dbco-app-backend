<?php
declare(strict_types=1);

namespace DBCO\Shared\Application\Responses;

use JsonSerializable;
use MinVWS\HealthCheck\Models\HealthCheckResultList;

/**
 * Health check response.
 *
 * @package DBCO\Shared\Application\Responses
 */
class HealthCheckResponse extends Response implements JsonSerializable
{
    /**
     * @var HealthCheckResultList
     */
    private HealthCheckResultList $result;

    /**
     * Constructor.
     *
     * @param HealthCheckResultList $result
     */
    public function __construct(HealthCheckResultList $result)
    {
        $this->result = $result;
    }

    /**
     * @inheritDoc
     */
    public function getStatusCode(): int
    {
        return $this->result->isHealthy ? 200 : 503;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return $this->result->jsonSerialize();
    }
}
