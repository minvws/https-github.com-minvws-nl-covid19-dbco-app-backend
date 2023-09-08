<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Dto\Calendar\CalendarResponseDto;
use App\Helpers\AuditObjectHelper;
use App\Models\Eloquent\EloquentBaseModel;
use App\Models\Eloquent\EloquentCase;
use App\Services\CalendarDataCalculationService;
use App\Services\CaseFragmentService;
use MinVWS\Audit\Models\AuditObject;
use Webmozart\Assert\Assert;

class ApiCaseFragmentController extends ApiAbstractFragmentController
{
    public function __construct(
        CaseFragmentService $caseFragmentService,
        private readonly CalendarDataCalculationService $calendarDataCalculationService,
    ) {
        parent::__construct($caseFragmentService);
    }

    /**
     * @inheritDoc
     */
    protected function objectForAuditEvent(string $ownerUuid, array $fragmentNames): AuditObject
    {
        $auditObject = AuditObject::create('case', $ownerUuid);
        AuditObjectHelper::setAuditObjectCountEdit($auditObject);
        $auditObject->detail('fragments', $fragmentNames);

        return $auditObject;
    }

    protected function addComputedData(EloquentBaseModel $owner): ?array
    {
        return $this->getCalendarResponseDto($owner)->toArray();
    }

    private function getCalendarResponseDto(EloquentBaseModel $owner): CalendarResponseDto
    {
        Assert::isInstanceOf($owner, EloquentCase::class);

        $periods = $this->calendarDataCalculationService->calculatePeriods($owner);
        $points = $this->calendarDataCalculationService->calculatePoints($owner);
        $views = $this->calendarDataCalculationService->getViews($owner);

        return new CalendarResponseDto(periods: $periods, points: $points, views: $views);
    }
}
