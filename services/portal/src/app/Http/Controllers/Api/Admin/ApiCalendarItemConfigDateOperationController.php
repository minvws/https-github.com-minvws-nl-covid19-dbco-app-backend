<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\Admin\DateOperation\UpdateDateOperationRequest;
use App\Http\Responses\Api\Admin\CalendarItemConfigEncoder;
use App\Http\Responses\Api\Admin\CalendarItemConfigStrategyEncoder;
use App\Http\Responses\Api\Admin\DateOperationEncoder;
use App\Http\Responses\EncodableResponse;
use App\Http\Responses\EncodableResponseBuilder;
use App\Models\Policy\CalendarItemConfig;
use App\Models\Policy\CalendarItemConfigStrategy;
use App\Models\Policy\DateOperation;
use App\Models\Policy\PolicyGuideline;
use App\Models\Policy\PolicyVersion;
use App\Services\Policy\Calendar\DateOperationService;
use MinVWS\Audit\Attribute\SetAuditEventDescription;
use MinVWS\Codable\EncodingContext;

final class ApiCalendarItemConfigDateOperationController extends ApiController
{
    public function __construct(private readonly DateOperationService $dateOperationService)
    {
        $this->authorizeResource(DateOperation::class);
    }

    #[SetAuditEventDescription('Calendar item config date operation updaten')]
    public function update(
        PolicyVersion $policyVersion,
        PolicyGuideline $policyGuideline,
        CalendarItemConfig $calendarItemConfig,
        CalendarItemConfigStrategy $calendarItemConfigStrategy,
        DateOperation $dateOperation,
        UpdateDateOperationRequest $updateDateOperationRequest,
    ): EncodableResponse
    {
        $this->dateOperationService->updateDateOperation($dateOperation, $updateDateOperationRequest->getDto());
        return $this->encodeCalendarItemConfigResponse($calendarItemConfig->refresh());
    }

    private function encodeCalendarItemConfigResponse(CalendarItemConfig $calendarItemConfig, int $status = 200): EncodableResponse
    {
        return EncodableResponseBuilder::create($calendarItemConfig, $status)
            ->withContext(static function (EncodingContext $context): void {
                $context->registerDecorator(CalendarItemConfig::class, new CalendarItemConfigEncoder());
                $context->registerDecorator(CalendarItemConfigStrategy::class, new CalendarItemConfigStrategyEncoder());
                $context->registerDecorator(DateOperation::class, new DateOperationEncoder());
            })
            ->build();
    }
}
