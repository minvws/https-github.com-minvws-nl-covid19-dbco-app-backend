<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Helpers\Config;
use App\Services\AuthenticationService;
use App\Services\CaseMetrics\CaseMetricsService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use MinVWS\Audit\Attribute\SetAuditEventDescription;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

use function in_array;
use function response;

class ApiCaseMetricsController extends ApiController
{
    public function __construct(
        private readonly CaseMetricsService $caseMetricService,
        private readonly AuthenticationService $authenticationService,
    ) {
    }

    #[SetAuditEventDescription('Aantallen gearchiveerde cases opgehaald voor stuurinformatie')]
    public function getCreatedArchived(Request $request): HttpResponse
    {
        $organisationUuid = $this->authenticationService->getSelectedOrganisation()?->uuid;
        if ($organisationUuid === null) {
            return $this->sendEmptyResponse();
        }

        $refreshedAt = $this->caseMetricService->getRefreshedAt($organisationUuid);
        if ($refreshedAt === null) {
            return $this->sendEmptyResponse();
        }

        $eTag = $this->caseMetricService->getCreatedArchivedETag($refreshedAt, $organisationUuid);
        if (in_array($eTag, $request->getETags(), true)) {
            return response('', HttpResponse::HTTP_NOT_MODIFIED);
        }

        return response()->json(
            [
                'refreshedAt' => $refreshedAt->format('c'),
                'eTag' => $eTag,
                'data' => $this->caseMetricService->getCreatedArchivedMetrics($organisationUuid),
            ],
            HttpResponse::HTTP_OK,
        );
    }

    public function refreshCreatedArchived(): Response
    {
        $organisationUuid = $this->authenticationService->getSelectedOrganisation()?->uuid;
        if ($organisationUuid !== null) {
            $periodEnd = CarbonImmutable::parse('now', Config::string('app.display_timezone'))
                ->setTimezone(Config::string('app.timezone'))
                ->setTime(0, 0);
            $this->caseMetricService->queueRefreshForOrganisation($organisationUuid, $periodEnd);
        }

        return response('', HttpResponse::HTTP_NO_CONTENT);
    }

    private function sendEmptyResponse(): JsonResponse
    {
        return response()->json(
            [
                'refreshedAt' => null,
                'eTag' => null,
                'data' => [],
            ],
            HttpResponse::HTTP_OK,
        );
    }
}
