<?php
declare(strict_types=1);

namespace DBCO\Shared\Application\Actions;

use DBCO\Shared\Application\Responses\HealthCheckResponse;
use MinVWS\HealthCheck\HealthChecker;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

/**
 * Health check action.
 *
 * @package DBCO\Shared\Application\Actions
 */
class HealthCheckAction extends Action
{
    /**
     * @var HealthChecker
     */
    private HealthChecker $healthChecker;

    /**
     * Constructor.
     *
     * @param LoggerInterface $logger
     * @param HealthChecker   $healthChecker
     */
    public function __construct(LoggerInterface $logger, HealthChecker $healthChecker)
    {
        parent::__construct($logger);
        $this->healthChecker = $healthChecker;
    }

    /**
     * @inheritdoc
     */
    protected function action(): Response
    {
        $result = $this->healthChecker->performHealthChecks();
        return $this->respond(new HealthCheckResponse($result));
    }
}
