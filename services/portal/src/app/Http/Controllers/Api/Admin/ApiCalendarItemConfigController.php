<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\Admin\CalendarItemConfig\UpdateCalendarItemConfigRequest;
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
use App\Services\Policy\Calendar\CalendarItemConfigService;
use Illuminate\Support\Collection;
use MinVWS\Audit\Attribute\SetAuditEventDescription;
use MinVWS\Codable\EncodingContext;

final class ApiCalendarItemConfigController extends ApiController
{
    public function __construct(private readonly CalendarItemConfigService $calendarItemConfigService)
    {
        $this->authorizeResource(CalendarItemConfig::class);
    }

    #[SetAuditEventDescription('Lijst van kalender item configs ophalen')]
    public function index(PolicyVersion $policyVersion, PolicyGuideline $policyGuideline): EncodableResponse
    {
        $calendarItemConfigs = $this->calendarItemConfigService->getCalendarItemConfigs($policyGuideline);
        return $this->encodeCalendarItemConfigResponse($calendarItemConfigs);
    }

    #[SetAuditEventDescription('Calendar item config updaten')]
    public function update(PolicyVersion $policyVersion, PolicyGuideline $policyGuideline, CalendarItemConfig $calendarItemConfig, UpdateCalendarItemConfigRequest $updateCalendarItemConfigRequest): EncodableResponse
    {
        $calendarItemConfig = $this->calendarItemConfigService->updateCalendarItemConfig(
            $calendarItemConfig,
            $updateCalendarItemConfigRequest->getDto(),
        );
        return $this->encodeCalendarItemConfigResponse($calendarItemConfig);
    }

    /**
     * @param CalendarItemConfig|Collection<CalendarItemConfig> $calendarItemConfig
     */
    private function encodeCalendarItemConfigResponse(CalendarItemConfig|Collection $calendarItemConfig, int $status = 200): EncodableResponse
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
