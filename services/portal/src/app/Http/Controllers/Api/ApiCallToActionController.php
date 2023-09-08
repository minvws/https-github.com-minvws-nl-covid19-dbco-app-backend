<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Dto\CallToActionHistory\CallToActionHistoryDto;
use App\Http\Controllers\Api\Traits\ValidatesModels;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CallToAction\CompleteRequest;
use App\Http\Requests\Api\CallToAction\CreateRequest;
use App\Http\Requests\Api\CallToAction\DropRequest;
use App\Http\Requests\Api\CallToAction\ListRequest;
use App\Http\Requests\Api\CallToAction\PickupRequest;
use App\Http\Responses\CallToAction\CallToActionEncoder;
use App\Http\Responses\CallToAction\CallToActionHistoryDtoEncoder;
use App\Http\Responses\CallToAction\CallToActionListEncoder;
use App\Http\Responses\EncodableResponse;
use App\Http\Responses\EncodableResponseBuilder;
use App\Models\CallToAction\ListOptions;
use App\Models\Eloquent\CallToAction;
use App\Services\AuthenticationService;
use App\Services\Chores\CallToActionHistoryService;
use App\Services\Chores\CallToActionService;
use App\Services\Chores\ChoreService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Response;
use MinVWS\Audit\Attribute\SetAuditEventDescription;
use MinVWS\Audit\Helpers\AuditEventHelper;
use MinVWS\Audit\Models\AuditEvent;
use MinVWS\Audit\Services\AuditService;
use MinVWS\Codable\EncodingContext;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use function response;

class ApiCallToActionController extends Controller
{
    use ValidatesModels;

    public function __construct(
        protected AuditService $auditService,
        protected AuthenticationService $authenticationService,
        protected CallToActionEncoder $callToActionEncoder,
        protected CallToActionListEncoder $callToActionListEncoder,
        protected CallToActionService $callToActionService,
        protected CallToActionHistoryService $callToActionHistoryService,
        protected CallToActionHistoryDtoEncoder $callToActionHistoryDtoEncoder,
        protected ChoreService $choreService,
    ) {
    }

    #[SetAuditEventDescription('lijst van taken ophalen')]
    public function listCallToActions(
        ListRequest $request,
    ): mixed {
        /** @var ListOptions $options */
        $options = $request->getDecodingContainer()->decodeObject(ListOptions::class);
        $organisation = $this->authenticationService->getRequiredSelectedOrganisation();
        $paginator = $this->callToActionService->listCallToActions($options, $organisation);

        return $this->auditService->registerHttpEvent(
            AuditEvent::create(__METHOD__, AuditEvent::ACTION_READ, AuditEventHelper::getAuditEventDescriptionByActionName(__METHOD__)),
            fn (AuditEvent $auditEvent) => EncodableResponseBuilder::create($paginator, Response::HTTP_OK)
                ->withContext(function (EncodingContext $context): void {
                    $context->registerDecorator(CallToAction::class, $this->callToActionListEncoder);
                })
                ->build()
        );
    }

    #[SetAuditEventDescription('taak details ophalen')]
    public function getCallToAction(
        CallToAction $callToAction,
    ): mixed {
        return $this->auditService->registerHttpEvent(
            AuditEvent::create(__METHOD__, AuditEvent::ACTION_READ, AuditEventHelper::getAuditEventDescriptionByActionName(__METHOD__)),
            fn (AuditEvent $auditEvent) => $this->encodableCallToActionResponse($callToAction)
        );
    }

    #[SetAuditEventDescription('maak een taak')]
    public function createCallToAction(
        CreateRequest $request,
    ): mixed {
        $organisation = $this->authenticationService->getRequiredSelectedOrganisation();
        $callToAction = $this->callToActionService->createCallToAction(
            $request->subject,
            $request->description,
            $request->organisation_uuid ?? $organisation->uuid,
            $request->resource_uuid,
            $request->resource_type,
            $request->resource_permission,
            $request->expires_at ?? null,
        );

        return $this->auditService->registerHttpEvent(
            AuditEvent::create(__METHOD__, AuditEvent::ACTION_CREATE, AuditEventHelper::getAuditEventDescriptionByActionName(__METHOD__)),
            fn (AuditEvent $auditEvent) => $this->encodableCallToActionResponse($callToAction)
        );
    }

    #[SetAuditEventDescription('pak een taak op')]
    public function pickupCallToAction(
        PickupRequest $request,
        CallToAction $callToAction,
    ): mixed {
        $expiresAt = CarbonImmutable::make($request->getStringOrNull(PickupRequest::FIELD_EXPIRES_AT));
        $this->callToActionService->pickupCallToAction($callToAction, $expiresAt);

        return $this->auditService->registerHttpEvent(
            AuditEvent::create(__METHOD__, AuditEvent::ACTION_READ, AuditEventHelper::getAuditEventDescriptionByActionName(__METHOD__)),
            fn (AuditEvent $auditEvent) => $this->encodableCallToActionResponse($callToAction)
        );
    }

    #[SetAuditEventDescription('geef een taak terug')]
    public function dropCallToAction(
        DropRequest $request,
        CallToAction $callToAction,
    ): mixed {
        return $this->auditService->registerHttpEvent(
            AuditEvent::create(__METHOD__, AuditEvent::ACTION_UPDATE, AuditEventHelper::getAuditEventDescriptionByActionName(__METHOD__)),
            function (AuditEvent $auditEvent) use ($request, $callToAction) {
                $this->callToActionService->dropCallToAction($callToAction, $request->getNote());

                return response()->json()->setStatusCode(Response::HTTP_NO_CONTENT);
            },
        );
    }

    #[SetAuditEventDescription('voltooi een taak')]
    public function completeCallToAction(
        CompleteRequest $request,
        CallToAction $callToAction,
    ): mixed {
        return $this->auditService->registerHttpEvent(
            AuditEvent::create(__METHOD__, AuditEvent::ACTION_UPDATE, AuditEventHelper::getAuditEventDescriptionByActionName(__METHOD__)),
            function (AuditEvent $auditEvent) use ($request, $callToAction) {
                $this->callToActionService->completeCallToAction($callToAction, $request->getNote());

                return response()->json()->setStatusCode(Response::HTTP_NO_CONTENT);
            },
        );
    }

    #[SetAuditEventDescription('lijst van gebeurtenissen met betrekking tot een taak')]
    public function listCallToActionHistory(string $uuid): mixed
    {
        $chore = $this->choreService->findPossiblyDeletedChoreByOwnerResourceId($uuid);

        if (!$chore) {
            throw new NotFoundHttpException('Chore does not exist');
        }

        $history = $this->callToActionHistoryService->getCallToActionHistoryForChore($chore);

        return $this->auditService->registerHttpEvent(
            AuditEvent::create(__METHOD__, AuditEvent::ACTION_READ, AuditEventHelper::getAuditEventDescriptionByActionName(__METHOD__)),
            fn (AuditEvent $auditEvent) => EncodableResponseBuilder::create($history, Response::HTTP_OK)
                ->withContext(function (EncodingContext $context): void {
                    $context->registerDecorator(CallToActionHistoryDto::class, $this->callToActionHistoryDtoEncoder);
                })
                ->build()
        );
    }

    /**
     * Encode the response
     */
    protected function encodableCallToActionResponse(mixed $data): EncodableResponse
    {
        return EncodableResponseBuilder::create($data)
            ->withContext(function (EncodingContext $context): void {
                $context->registerDecorator(CallToAction::class, $this->callToActionEncoder);
            })->build();
    }
}
