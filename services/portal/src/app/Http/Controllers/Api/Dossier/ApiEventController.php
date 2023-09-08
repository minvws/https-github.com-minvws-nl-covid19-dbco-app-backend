<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Dossier;

use App\Http\Controllers\Api\ApiController;
use App\Http\Responses\EncodableResponse;
use App\Models\Disease\Entity;
use App\Models\Dossier\Dossier;
use App\Models\Dossier\Event;
use App\Schema\Validation\ValidationResult;
use App\Services\Dossier\EventService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\ParameterBag;

use function route;

class ApiEventController extends ApiController
{
    public function __construct(private readonly EventService $eventService)
    {
    }

    public function show(Event $event): EncodableResponse
    {
        $validationResult = $this->eventService->validateEvent($event->dossier, $event, null);
        return $this->responseForEvent($event, $validationResult);
    }

    public function create(Dossier $dossier, Request $request): EncodableResponse
    {
        $event = $this->eventService->makeEvent($dossier);
        return $this->handleCreateOrUpdate($dossier, $event, $request);
    }

    public function update(Event $event, Request $request): EncodableResponse
    {
        return $this->handleCreateOrUpdate($event->dossier, $event, $request);
    }

    public function validateForCreate(Dossier $dossier, Request $request): EncodableResponse
    {
        return $this->handleValidateForCreateOrUpdate($dossier, null, $request);
    }

    public function validateForUpdate(Event $event, Request $request): EncodableResponse
    {
        return $this->handleValidateForCreateOrUpdate($event->dossier, $event, $request);
    }

    private function handleCreateOrUpdate(Dossier $dossier, Event $event, Request $request): EncodableResponse
    {
        $data = $request->json() instanceof ParameterBag ? $request->json()->all() : [];

        $validationResult = $this->eventService->validateEvent($dossier, $event, $data);
        if (!$validationResult->isValid()) {
            return new EncodableResponse(['validationResult' => $validationResult], 400);
        }

        $isNew = !$event->exists;
        $event = $this->eventService->decodeEvent($event, $data);
        $this->eventService->saveEvent($event);

        return $this->responseForEvent($event, $validationResult, $isNew ? 201 : 200);
    }

    private function handleValidateForCreateOrUpdate(Dossier $dossier, ?Event $event, Request $request): EncodableResponse
    {
        $data = $request->json() instanceof ParameterBag ? $request->json()->all() : [];
        $validationResult = $this->eventService->validateEvent($dossier, $event, $data);
        return new EncodableResponse(['validationResult' => $validationResult], $validationResult->isValid() ? 200 : 400);
    }

    private function responseForEvent(Event $event, ValidationResult $validationResult, int $status = 200): EncodableResponse
    {
        $data = $this->eventService->encodeEvent($event);
        $form = route(
            'api-disease-model-show-form',
            ['disease' => $event->dossier->diseaseModel->disease->id, 'diseaseModelVersion' => $event->dossier->diseaseModel->id, 'entityName' => Entity::Event->value],
        );
        return new EncodableResponse(['data' => $data, 'validationResult' => $validationResult, 'form' => $form], $status);
    }
}
