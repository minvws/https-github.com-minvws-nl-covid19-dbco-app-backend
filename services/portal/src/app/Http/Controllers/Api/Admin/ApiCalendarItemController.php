<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Admin\Attributes\RequestHasFixedValuesQueryFilter;
use App\Http\Controllers\Api\Admin\Attributes\RequestQueryFilter;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\Admin\CalendarItem\CreateCalendarItemRequest;
use App\Http\Requests\Api\Admin\CalendarItem\UpdateCalendarItemRequest;
use App\Http\Responses\Api\Admin\CalendarItemEncoder;
use App\Http\Responses\EncodableResponse;
use App\Http\Responses\EncodableResponseBuilder;
use App\Models\Policy\CalendarItem;
use App\Models\Policy\PolicyVersion;
use App\Services\CalendarItemService;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use MinVWS\Audit\Attribute\SetAuditEventDescription;
use MinVWS\Codable\EncodingContext;
use MinVWS\DBCO\Enum\Models\PolicyPersonType;

final class ApiCalendarItemController extends ApiController
{
    #[RequestQueryFilter('person')]
    protected ?PolicyPersonType $policyPersonType = null;

    public function __construct(private readonly CalendarItemService $calendarItemService)
    {
        $this->authorizeResource(CalendarItem::class);
    }

    #[SetAuditEventDescription('Lijst van kalender items ophalen')]
    #[RequestHasFixedValuesQueryFilter('person', PolicyPersonType::class, required: false)]
    public function index(PolicyVersion $policyVersion): EncodableResponse
    {
        $calendarItems = $this->calendarItemService->getCalendarItems($policyVersion->uuid, $this->policyPersonType);

        return $this->encodeCalendarItemResponse($calendarItems);
    }

    #[SetAuditEventDescription('Calendar item ophalen')]
    public function show(PolicyVersion $policyVersion, CalendarItem $calendarItem): EncodableResponse
    {
        return $this->encodeCalendarItemResponse($calendarItem);
    }

    #[SetAuditEventDescription('Calendar item verwijderen')]
    public function destroy(PolicyVersion $policyVersion, CalendarItem $calendarItem, ResponseFactory $response): Response|JsonResponse
    {
        return $this->calendarItemService->deleteCalendarItem($calendarItem)
            ? $response->noContent()
            : $response->json(status: 404);
    }

    #[SetAuditEventDescription('Calendar item aanmaken')]
    public function store(PolicyVersion $policyVersion, CreateCalendarItemRequest $request): EncodableResponse
    {
        $calendarItem = $this->calendarItemService->createCalendarItem($policyVersion->uuid, $request->getDto());

        return $this->encodeCalendarItemResponse($calendarItem);
    }

    #[SetAuditEventDescription('Calendar item updaten')]
    public function update(PolicyVersion $policyVersion, CalendarItem $calendarItem, UpdateCalendarItemRequest $request): EncodableResponse
    {
        $calendarItem = $this->calendarItemService->updateCalendarItem(
            $calendarItem->setRelation('policyVersion', $policyVersion),
            $request->getDto(),
        );

        return $this->encodeCalendarItemResponse($calendarItem);
    }

    /**
     * @param CalendarItem|Collection<CalendarItem> $calendarItem
     */
    private function encodeCalendarItemResponse(CalendarItem|Collection $calendarItem, int $status = 200): EncodableResponse
    {
        return EncodableResponseBuilder::create($calendarItem, $status)
            ->withContext(static function (EncodingContext $context): void {
                $context->registerDecorator(CalendarItem::class, new CalendarItemEncoder());
            })
            ->build();
    }
}
