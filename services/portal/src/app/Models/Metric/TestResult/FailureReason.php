<?php

declare(strict_types=1);

namespace App\Models\Metric\TestResult;

use App\Exceptions\OrganisationNotFoundException;
use App\Exceptions\TestResultReport\CouldNotDecodePayload;
use App\Exceptions\TestResultReport\CouldNotDecryptPayload;
use App\Models\Metric\CounterMetric;
use Throwable;

final class FailureReason extends CounterMetric
{
    protected string $name = 'test_result_report_import:failure_reason_counter';
    protected string $help = 'Counts the different reasons why a test result report import could fail';

    private function __construct(string $failureReason)
    {
        $this->labels = ['failureReason' => $failureReason];
    }

    public static function fromThrowable(Throwable $throwable): self
    {
        $failureReason = match ($throwable::class) {
            CouldNotDecryptPayload::class => 'message_decryption_failed',
            CouldNotDecodePayload::class => 'message_decoding_failed',
            OrganisationNotFoundException::class => 'organisation_not_found',
            default => 'unknown',
        };

        return new self($failureReason);
    }
}
