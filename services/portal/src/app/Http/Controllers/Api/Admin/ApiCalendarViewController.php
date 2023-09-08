<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\Admin\CalendarView\UpdateCalendarViewRequest;
use App\Http\Responses\Api\Admin\CalendarItemEncoder;
use App\Http\Responses\Api\Admin\CalendarViewEncoder;
use App\Http\Responses\EncodableResponse;
use App\Http\Responses\EncodableResponseBuilder;
use App\Models\Policy\CalendarItem;
use App\Models\Policy\CalendarView;
use App\Models\Policy\PolicyVersion;
use App\Services\Calendar\CalendarViewService;
use Illuminate\Support\Collection;
use MinVWS\Audit\Attribute\SetAuditEventDescription;
use MinVWS\Codable\EncodingContext;

final class ApiCalendarViewController extends ApiController
{
    public function __construct(private readonly CalendarViewService $calendarViewService)
    {
        $this->authorizeResource(CalendarView::class);
    }

    #[SetAuditEventDescription('Calendar views ophalen')]
    public function index(PolicyVersion $policyVersion): EncodableResponse
    {
        $calendarViews = $this->calendarViewService->getCalendarViews($policyVersion->uuid);

        return $this->encodeCalendarViewResponse($calendarViews);
    }

    #[SetAuditEventDescription('Calendar view ophalen')]
    public function show(PolicyVersion $policyVersion, CalendarView $calendarView): EncodableResponse
    {
        return $this->encodeCalendarViewResponse($calendarView);
    }

    #[SetAuditEventDescription('Calendar view updaten')]
    public function update(PolicyVersion $policyVersion, CalendarView $calendarView, UpdateCalendarViewRequest $request): EncodableResponse
    {
        $calendarView = $this->calendarViewService->updateCalendarView(
            $calendarView->setRelation('policyVersion', $policyVersion),
            $request->getDto(),
        );

        return $this->encodeCalendarViewResponse($calendarView);
    }

    /**
     * @param CalendarView|Collection<CalendarView> $calendarView
     */
    private function encodeCalendarViewResponse(CalendarView|Collection $calendarView, int $status = 200): EncodableResponse
    {
        return EncodableResponseBuilder::create($calendarView, $status)
            ->withContext(static function (EncodingContext $context): void {
                $context->registerDecorator(CalendarView::class, new CalendarViewEncoder());
                $context->registerDecorator(CalendarItem::class, new CalendarItemEncoder());
            })
            ->build();
    }
}
