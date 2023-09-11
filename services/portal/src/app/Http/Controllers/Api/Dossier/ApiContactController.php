<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Dossier;

use App\Http\Controllers\Api\ApiController;
use App\Http\Responses\EncodableResponse;
use App\Models\Disease\Entity;
use App\Models\Dossier\Contact;
use App\Models\Dossier\Dossier;
use App\Schema\Validation\ValidationResult;
use App\Services\Dossier\ContactService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\ParameterBag;

use function array_filter;
use function count;
use function is_string;
use function response;
use function route;

class ApiContactController extends ApiController
{
    public function __construct(private readonly ContactService $contactService)
    {
    }

    private function getView(Request $request): ?string
    {
        $view = $request->query('view');
        return is_string($view) ? $view : null;
    }

    public function show(Contact $contact, Request $request): EncodableResponse
    {
        $view = $this->getView($request);
        $validationResult = $this->contactService->validateContact($contact->dossier, $contact, null, $view);
        return $this->responseForContact($contact, $validationResult, $view);
    }

    public function create(Dossier $dossier, Request $request): EncodableResponse
    {
        $contact = $this->contactService->makeContact($dossier);
        return $this->handleCreateOrUpdate($dossier, $contact, $request);
    }

    public function update(Contact $contact, Request $request): EncodableResponse
    {
        return $this->handleCreateOrUpdate($contact->dossier, $contact, $request);
    }

    public function validateForCreate(Dossier $dossier, Request $request): EncodableResponse
    {
        return $this->handleValidateForCreateOrUpdate($dossier, null, $request);
    }

    public function validateForUpdate(Contact $contact, Request $request): EncodableResponse
    {
        return $this->handleValidateForCreateOrUpdate($contact->dossier, $contact, $request);
    }

    public function delete(Contact $contact): Response
    {
        $this->contactService->deleteContact($contact);
        return response(status: 204);
    }

    private function handleCreateOrUpdate(Dossier $dossier, Contact $contact, Request $request): EncodableResponse
    {
        $data = $request->json() instanceof ParameterBag ? $request->json()->all() : [];
        $view = $this->getView($request);

        $validationResult = $this->contactService->validateContact($dossier, $contact, $data, $view);
        if (!$validationResult->isValid()) {
            return new EncodableResponse(['validationResult' => $validationResult], 400);
        }

        $isNew = !$contact->exists;
        $contact = $this->contactService->decodeContact($contact, $data, $view);
        $this->contactService->saveContact($contact);

        return $this->responseForContact($contact, $validationResult, $view, $isNew ? 201 : 200);
    }

    private function handleValidateForCreateOrUpdate(Dossier $dossier, ?Contact $contact, Request $request): EncodableResponse
    {
        $data = $request->json() instanceof ParameterBag ? $request->json()->all() : [];
        $view = $this->getView($request);
        $validationResult = $this->contactService->validateContact($dossier, $contact, $data, $view);
        return new EncodableResponse(['validationResult' => $validationResult], $validationResult->isValid() ? 200 : 400);
    }

    private function responseForContact(Contact $contact, ValidationResult $validationResult, ?string $view, int $status = 200): EncodableResponse
    {
        $data = $this->contactService->encodeContact($contact, $view);

        $extra = array_filter(['view' => $view]);

        $links = [];

        $links['self'] = ['href' => route('api-contact-show', ['dossierContact' => $contact, ...$extra])];
        $links['update'] = ['href' => route('api-contact-update', ['dossierContact' => $contact, ...$extra]), 'method' => 'PUT'];
        $links['delete'] = ['href' => route('api-contact-delete', ['dossierContact' => $contact, ...$extra]), 'method' => 'DELETE'];

        if (count($extra) > 0) {
            $links['editModal'] = ['href' => route('api-contact-show', ['dossierContact' => $contact])];
        } else {
            $links['form'] = [
                'href' => route(
                    'api-disease-model-show-form',
                    ['disease' => $contact->dossier->diseaseModel->disease->id, 'diseaseModelVersion' => $contact->dossier->diseaseModel->version, 'entityName' => Entity::Contact->value],
                )];
        }

        return new EncodableResponse(
            array_filter(['view' => $view, 'data' => $data, 'validationResult' => $validationResult, 'links' => $links]),
            $status,
        );
    }
}
