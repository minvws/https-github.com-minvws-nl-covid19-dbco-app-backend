<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Exceptions\TestResultReport\CouldNotDecodePayload;
use App\Exceptions\TestResultReport\CouldNotDecryptPayload;
use App\Services\TestResult\EncryptionService;
use App\Services\TestResult\TestResultReportImportHandler;
use Illuminate\Contracts\Queue\ShouldQueue;
use Throwable;

use function is_array;
use function json_decode;

final class ImportTestResultReport implements ShouldQueue
{
    public function __construct(
        private readonly string $messageId,
        private readonly string $payload,
    ) {
    }

    /**
     * @throws Throwable
     */
    public function handle(TestResultReportImportHandler $testResultReportImportHandler): void
    {
        $testResultReportImportHandler->handle($this);
    }

    public function getMessageId(): string
    {
        return $this->messageId;
    }

    /**
     * @throws CouldNotDecryptPayload
     * @throws CouldNotDecodePayload
     */
    public function getPayload(EncryptionService $encryptionService): array
    {
        try {
            $decryptedPayload = $encryptionService->decryptPayload($this->payload);
        } catch (Throwable $throwable) {
            throw CouldNotDecryptPayload::fromThrowable($throwable);
        }

        $decodedPayload = json_decode($decryptedPayload, true);
        if (!is_array($decodedPayload)) {
            throw new CouldNotDecodePayload();
        }

        return $decodedPayload;
    }
}
