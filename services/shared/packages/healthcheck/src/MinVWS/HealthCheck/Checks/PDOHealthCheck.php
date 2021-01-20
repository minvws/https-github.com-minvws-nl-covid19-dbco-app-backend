<?php
namespace MinVWS\HealthCheck\Checks;

use Exception;
use MinVWS\HealthCheck\Models\HealthCheckResult;
use PDO;

/**
 * PDO health check.
 *
 * @package MinVWS\HealthCheck\Checks
 */
class PDOHealthCheck implements HealthCheck
{
    /**
     * @var PDO
     */
    private PDO $client;

    /**
     * Constructor.
     *
     * @param PDO $client
     */
    public function __construct(PDO $client)
    {
        $this->client = $client;
    }

    /**
     * @inheritDoc
     */
    public function performHealthCheck(): HealthCheckResult
    {
        try {
            if ($this->client->query('SELECT 1') ||
                $this->client->query('SELECT 1 FROM dual')) {
                return new HealthCheckResult(true);
            }
        } catch (Exception $e) {
        }

        return new HealthCheckResult(false);
    }
}