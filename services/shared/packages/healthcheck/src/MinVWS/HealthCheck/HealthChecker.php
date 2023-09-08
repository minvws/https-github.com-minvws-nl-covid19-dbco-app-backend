<?php

namespace MinVWS\HealthCheck;

use MinVWS\HealthCheck\Checks\HealthCheck;
use MinVWS\HealthCheck\Models\HealthCheckResultList;

/**
 * Health checker.
 */
class HealthChecker
{
    /**
     * @var HealthCheck[]
     */
    private array $healthChecks = [];

    /**
     * Add health check.
     *
     * @param string      $service      Unique service label.
     * @param HealthCheck $healthCheck  Health check.
     */
    public function addHealthCheck(string $service, HealthCheck $healthCheck)
    {
        $this->healthChecks[$service] = $healthCheck;
    }

    /**
     * Perform health checks.
     *
     * @return HealthCheckResultList
     */
    public function performHealthChecks(): HealthCheckResultList
    {
        $result = new HealthCheckResultList();

        foreach ($this->healthChecks as $service => $healthCheck) {
            $result->addHealthCheckResult($service, $healthCheck->performHealthCheck());
        }

        return $result;
    }
}
