<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Helpers\AuditObjectHelper;
use App\Http\Controllers\Controller;
use App\Http\Responses\EncodableResponse;
use App\Http\Responses\EncodableResponseBuilder;
use App\Http\Responses\Sync\SyncEncoder;
use App\Models\Eloquent\EloquentCase;
use App\Services\CaseFragmentService;
use Exception;
use MinVWS\Audit\Models\AuditEvent;
use MinVWS\Audit\Models\AuditObject;
use MinVWS\Codable\EncodingContext;

class ApiCaseSyncController extends Controller
{
    private CaseFragmentService $caseFragmentService;

    public function __construct(
        CaseFragmentService $caseFragmentService,
    ) {
        $this->caseFragmentService = $caseFragmentService;
    }

    /**
     * @throws Exception
     */
    public function getCaseFragments(
        EloquentCase $eloquentCase,
        SyncEncoder $syncEncoder,
        AuditEvent $auditEvent,
    ): EncodableResponse {
        $caseAuditObject = AuditObject::create('case', $eloquentCase->uuid);
        AuditObjectHelper::setAuditObjectOrganisation($caseAuditObject, $eloquentCase);
        $auditEvent->object($caseAuditObject);

        $fragments = $this->caseFragmentService->loadFragments($eloquentCase->uuid, [
            'abroad',
            'alternateContact',
            'alternateResidency',
            'alternativeLanguage',
            'contacts',
            'deceased',
            'eduDaycare',
            'general',
            'generalPractitioner',
            'groupTransport',
            'hospital',
            'housemates',
            'index',
            'job',
            'medication',
            'pregnancy',
            'principalContextualSettings',
            'recentBirth',
            'riskLocation',
            'symptoms',
            'test',
            'underlyingSuffering',
            'vaccination',
            'communication',
            'immunity',
            'extensiveContactTracing',
            'sourceEnvironments',
            'generalPractitioner',
        ]);

        $data = [
            'uuid' => $eloquentCase->uuid,
            'hpZoneNumber' => $eloquentCase->hpZoneNumber,
            'fragments' => $fragments,
        ];

        return
            EncodableResponseBuilder::create($data)
            ->withContext(static function (EncodingContext $context) use ($syncEncoder): void {
                    $context->registerDecorator(EloquentCase::class, $syncEncoder);
            })
                ->build();
    }
}
