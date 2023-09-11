<?php

namespace MinVWS\HealthCheck\Checks;

use Closure;
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
     * @var Closure
     */
    private Closure $pdoGetter;

    /**
     * Constructor.
     *
     * @param Closure $pdoGetter Callback that returns the PDO object.
     */
    public function __construct(Closure $pdoGetter)
    {
        $this->pdoGetter = $pdoGetter;
    }

    /**
     * @inheritDoc
     */
    public function performHealthCheck(): HealthCheckResult
    {
        try {
            /** @var $client PDO */
            $client = call_user_func($this->pdoGetter);

            if (
                $client->query('SELECT 1') ||
                $client->query('SELECT 1 FROM dual')
            ) {
                return new HealthCheckResult(true);
            } else {
                return new HealthCheckResult(false);
            }
        } catch (Exception $e) {
            return new HealthCheckResult(false, 'internalError', $e->getMessage());
        }
    }
}
