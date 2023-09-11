<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ReportTestResultRequest;
use App\Services\JwtTokenService;
use App\Services\TestResultReportService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\ValidationException;
use MinVWS\Audit\Attribute\SetAuditEventDescription;
use MinVWS\Audit\Models\AuditEvent;
use MinVWS\Audit\Models\AuditObject;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Webmozart\Assert\Assert;

use function json_decode;

use const JSON_THROW_ON_ERROR;

final class ReportTestResultController extends Controller
{
    public function __construct(
        private readonly JwtTokenService $jwtTokenService,
        private readonly LoggerInterface $logger,
        private readonly TestResultReportService $testResultReportService,
    ) {
    }

    /**
     * @throws ValidationException
     */
    #[SetAuditEventDescription('Ontvang test resultaat via API toegang')]
    public function __invoke(Request $request, AuditEvent $auditEvent): JsonResponse
    {
        $auditEvent->object($auditObject = AuditObject::create('processTestResultReport'));

        try {
            $jwtPayload = $this->jwtTokenService->getJwtPayloadFromRequest($request);
            $testResultPayload = (array) json_decode(
                $jwtPayload['http://ggdghor.nl/payload'],
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (Exception $exception) {
            $this->logger->info('Invalid JWT payload', ['exception' => $exception]);
            return new JsonResponse('Invalid JWT payload', Response::HTTP_BAD_REQUEST);
        }

        $this->logger->debug('Received new test result report', [
            'payload' => $testResultPayload,
            'messageId' => $testResultPayload['messageId'] ?? null,
        ]);

        $reportTestResultRequest = new ReportTestResultRequest($testResultPayload);
        $validator = $reportTestResultRequest->makeValidator();

        if ($validator->fails()) {
            $this->logger->info('Validation of test result report fails', [
                'errors' => $validator->errors(),
                'messageId' => $testResultPayload['messageId'] ?? null,
            ]);
        }

        $validator->validate();

        $messageId = $testResultPayload['messageId'] ?? null;
        Assert::string($messageId);

        $this->testResultReportService->save($messageId, $validator->validated());

        $auditObject->identifier($messageId);

        return new JsonResponse(null, Response::HTTP_ACCEPTED);
    }
}
