<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Case\TestResult;

use App\Http\Controllers\Api\ApiController;
use App\Http\Responses\Api\TestResult\TestResultEncoder;
use App\Http\Responses\EncodableResponse;
use App\Http\Responses\EncodableResponseBuilder;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\TestResult;
use App\Services\TestResult\TestResultService;
use Illuminate\Http\Response;
use MinVWS\Audit\Attribute\SetAuditEventDescription;
use MinVWS\Audit\Models\AuditEvent;
use MinVWS\Audit\Models\AuditObject;
use MinVWS\Codable\EncodingContext;

use function response;

class ApiCaseTestResultController extends ApiController
{
    public function __construct(
        private readonly TestResultService $testResultService,
    ) {
    }

    #[SetAuditEventDescription('Testresultaten opgehaald')]
    public function getCaseTestResults(
        AuditEvent $auditEvent,
        TestResultEncoder $testResultEncoder,
        EloquentCase $eloquentCase,
    ): EncodableResponse {
        $testResults = $this->testResultService->getByCase($eloquentCase);

        /** @var array<AuditObject> $auditObjects */
        $auditObjects = $testResults->map(static function (TestResult $testResult): AuditObject {
            return AuditObject::create('testResult', $testResult->uuid);
        })->toArray();
        $auditEvent->objects($auditObjects);

        return
            EncodableResponseBuilder::create($testResults)
            ->withContext(static function (EncodingContext $context) use ($testResultEncoder): void {
                $context->registerDecorator(TestResult::class, $testResultEncoder);
            })
            ->build();
    }

    #[SetAuditEventDescription('Testresultaat toegevoegd')]
    public function createManualTestResult(
        AuditEvent $auditEvent,
        CreateManualTestResultRequest $request,
        EloquentCase $case,
        TestResultEncoder $testResultEncoder,
    ): EncodableResponse {
        /** @var CreateManualTest $createManualTest */
        $createManualTest = $request->getDecodingContainer()->decodeObject(CreateManualTest::class);

        $testResult = $this->testResultService->createManualTestResult($case, $createManualTest);

        $auditEvent->object(AuditObject::create("testResult", $testResult->uuid));

        return
            EncodableResponseBuilder::create($testResult)
            ->withContext(static function (EncodingContext $context) use ($testResultEncoder): void {
                    $context->registerDecorator(TestResult::class, $testResultEncoder);
            })
                ->build();
    }

    #[SetAuditEventDescription('Testresultaat verwijderd')]
    public function deleteTestResult(
        AuditEvent $auditEvent,
        EloquentCase $case,
        string $testResultUuid,
    ): Response {
        $this->testResultService->deleteByUuid($testResultUuid, $case);

        $auditEvent->object(AuditObject::create("testResult", $testResultUuid));

        return response()->noContent();
    }
}
