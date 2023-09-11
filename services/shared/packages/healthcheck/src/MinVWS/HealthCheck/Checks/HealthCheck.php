<?php

namespace MinVWS\HealthCheck\Checks;

use MinVWS\HealthCheck\Models\HealthCheckResult;

/**
 * Interface for health checks.
 *
 * @package MinVWS\HealthCheck\Checks
 */
interface HealthCheck
{
    /**
     * Performs the health check.
     *
     * @return HealthCheckResult
     */
    public function performHealthCheck(): HealthCheckResult;
}
