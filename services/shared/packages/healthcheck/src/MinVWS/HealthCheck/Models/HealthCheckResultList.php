<?php

namespace MinVWS\HealthCheck\Models;

use JsonSerializable;

/**
 * List of health check results.
 */
class HealthCheckResultList implements JsonSerializable
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
     * @param string            $service
     * @param HealthCheckResult $result
     */
    public function addHealthCheckResult(string $service, HealthCheckResult $result)
    {
        $this->isHealthy = $this->isHealthy && $result->isHealthy;
        $this->results[$service] = $result;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array
    {
        $data = ['isHealthy' => $this->isHealthy, 'results' => []];

        foreach ($this->results as $service => $result) {
            $entry = ['service' => $service];
            $entry = array_merge($entry, $result->jsonSerialize());
            $data['results'][] = $entry;
        }

        return $data;
    }
}
