<?php

namespace MinVWS\HealthCheck\Checks;

use Closure;
use Exception;
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
     * @var Closure
     */
    private Closure $predisGetter;

    /**
     * Constructor.
     *
     * @param Closure $predisGetter
     */
    public function __construct(Closure $predisGetter)
    {
        $this->predisGetter = $predisGetter;
    }

    /**
     * @inheritDoc
     */
    public function performHealthCheck(): HealthCheckResult
    {
        try {
            /** @var $client PredisClient */
            $client = call_user_func($this->predisGetter);

            if ((string)$client->ping() === 'PONG') {
                return new HealthCheckResult(true);
            } else {
                return new HealthCheckResult(false);
            }
        } catch (Exception $e) {
            error_clear_last(); // error is also registered as PHP error
            return new HealthCheckResult(false, 'internalError', $e->getMessage());
        }
    }
}
