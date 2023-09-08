<?php

declare(strict_types=1);

namespace App\Services;

use App\Jobs\ImportTestResultReport;
use Carbon\CarbonImmutable;
use Psr\Log\LoggerInterface;

use function dispatch;

class TestResultReportService
{
    public function __construct(
        private readonly EncryptionService $encryptionService,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function save(string $messageId, array $payload): void
    {
        $receivedAt = CarbonImmutable::now()->toDateTimeString('microsecond');
        $payload['receivedAt'] = $receivedAt;

        $encryptedPayload = $this->encryptionService->encryptPayload($payload);
        $this->logger->info('Encrypted test result report', ['messageId' => $messageId]);

        dispatch(new ImportTestResultReport($messageId, $encryptedPayload));
        $this->logger->info(
            'Dispatched job for later processing',
            [
                'messageId' => $messageId,
                'receivedAt' => $receivedAt,
            ],
        );
    }
}
