<?php
namespace MinVWS\HealthCheck;

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
     * @param string      $label        Unique label.
     * @param HealthCheck $healthCheck  Health check.
     */
    public function addHealthCheck(string $label, HealthCheck $healthCheck)
    {
        $this->healthChecks[$label] = $healthCheck;
    }

    /**
     * Perform health checks.
     *
     * @return HealthCheckResultList
     */
    public function performHealthChecks(): HealthCheckResultList
    {
        $result = new HealthCheckResultList();

        foreach ($this->healthChecks as $label => $healthCheck) {
            $result->addHealthCheckResult($label, $healthCheck->performHealthCheck());
        }

        return $result;
    }
}