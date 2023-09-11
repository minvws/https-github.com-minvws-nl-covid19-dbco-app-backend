<?php

declare(strict_types=1);

namespace DBCO\Shared\Application\Actions;

use DBCO\Shared\Application\Responses\HealthCheckResponse;
use MinVWS\HealthCheck\HealthChecker;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use MinVWS\Audit\AuditService;

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
     * @param AuditService    $auditService
     * @param HealthChecker   $healthChecker
     */
    public function __construct(
        LoggerInterface $logger,
        AuditService $auditService,
        HealthChecker $healthChecker
    ) {
        parent::__construct($logger, $auditService);
        $this->healthChecker = $healthChecker;
    }

    /**
     * @inheritdoc
     */
    protected function action(): Response
    {
        $this->auditService->setEventExpected(false);

        $result = $this->healthChecker->performHealthChecks();
        return $this->respond(new HealthCheckResponse($result));
    }
}
