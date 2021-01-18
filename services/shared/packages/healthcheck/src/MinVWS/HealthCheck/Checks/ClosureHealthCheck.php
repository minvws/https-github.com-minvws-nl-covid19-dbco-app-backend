<?php
namespace MinVWS\HealthCheck\Checks;

use Closure;
use MinVWS\HealthCheck\Models\HealthCheckResult;

/**
 * Health check that accepts a closure that does the actual health check.
 *
 * @package MinVWS\HealthCheck\Checks
 */
class ClosureHealthCheck implements HealthCheck
{
    /**
     * @var Closure
     */
    private Closure $closure;

    /**
     * Constructor.
     *
     * @param Closure $closure
     */
    public function __construct(Closure $closure)
    {
        $this->closure = $closure;
    }

    /**
     * @inheritDoc
     */
    public function performHealthCheck(): HealthCheckResult
    {
        return call_user_func($this->closure);
    }
}