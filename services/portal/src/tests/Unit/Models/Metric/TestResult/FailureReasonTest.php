<?php

declare(strict_types=1);

namespace Tests\Unit\Models\Metric\TestResult;

use App\Exceptions\OrganisationNotFoundException;
use App\Exceptions\TestResultReport\CouldNotDecodePayload;
use App\Exceptions\TestResultReport\CouldNotDecryptPayload;
use App\Models\Metric\TestResult\FailureReason;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;
use Throwable;

#[Group('metric')]
class FailureReasonTest extends UnitTestCase
{
    #[DataProvider('throwableDataProvider')]
    public function testMetric(Throwable $throwable, string $failureReason): void
    {
        $metric = FailureReason::fromThrowable($throwable);

        $this->assertEquals(['failureReason' => $failureReason], $metric->getLabels());
    }

    public static function throwableDataProvider(): array
    {
        return [
            'message_decryption_failed' => [new CouldNotDecryptPayload(), 'message_decryption_failed'],
            'message_decoding_failed' => [new CouldNotDecodePayload(), 'message_decoding_failed'],
            'organisation_not_found' => [
                OrganisationNotFoundException::withHpZoneCode('someHpZoneCode', new ModelNotFoundException()),
                'organisation_not_found',
            ],
            'unknown' => [new ModelNotFoundException(), 'unknown'],
        ];
    }
}
