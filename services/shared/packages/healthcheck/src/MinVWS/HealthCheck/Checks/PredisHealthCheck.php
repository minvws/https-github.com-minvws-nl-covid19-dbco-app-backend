<?php
namespace MinVWS\HealthCheck\Checks;

use MinVWS\HealthCheck\Models\HealthCheckResult;
use Predis\Client as PredisClient;

/**
 * Redis health check.
 *
 * @package MinVWS\HealthCheck\Checks
 */
class PredisHealthCheck implements HealthCheck
{
    /**
     * @var PredisClient
     */
    private PredisClient $client;

    /**
     * Constructor.
     *
     * @param PredisClient $client
     */
    public function __construct(PredisClient $client)
    {
        $this->client = $client;
    }

    /**
     * @inheritDoc
     */
    public function performHealthCheck(): HealthCheckResult
    {
        if ((string)$this->client->ping() === 'PONG') {
            return new HealthCheckResult(true);
        } else {
            return new HealthCheckResult(false);
        }
    }
}