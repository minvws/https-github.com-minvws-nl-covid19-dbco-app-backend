<?php
namespace MinVWS\HealthCheck\Models;

/**
 * List of health check results.
 */
class HealthCheckResultList
{
    /**
     * @var bool
     */
    public bool $isHealthy = true;

    /**
     * @var HealthCheckResult[]
     */
    public array $results = [];

    /**
     * Add result.
     *
     * @param string            $label
     * @param HealthCheckResult $result
     */
    public function addHealthCheckResult(string $label, HealthCheckResult $result)
    {
        $this->isHealthy = $this->isHealthy && $result->isHealthy;
        $this->results[$label] = $result;
    }
}