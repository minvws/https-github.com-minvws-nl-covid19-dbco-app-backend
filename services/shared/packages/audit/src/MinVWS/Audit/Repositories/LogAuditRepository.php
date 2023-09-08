<?php

namespace MinVWS\Audit\Repositories;

use JsonException;
use MinVWS\Audit\Encoder\AuditEventEncoder;
use MinVWS\Audit\Models\AuditEvent;
use Psr\Log\LoggerInterface;

final class LogAuditRepository implements AuditRepository
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @throws JsonException
     */
    public function registerEvent(AuditEvent $event): void
    {
        $json = AuditEventEncoder::encodeAsJson($event);
        $this->logger->info($json);
    }
}
