<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Dossier;

use App\Http\Controllers\Api\ApiController;
use App\Http\Responses\EncodableResponse;
use App\Models\Disease\Disease;
use App\Models\Disease\DiseaseModel;
use App\Models\Disease\Entity;
use App\Models\Dossier\Dossier;
use App\Schema\Validation\ValidationResult;
use App\Services\AuthenticationService;
use App\Services\Dossier\DossierService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\ParameterBag;

use function array_filter;
use function route;

class ApiDossierController extends ApiController
{
    public function __construct(private readonly DossierService $dossierService)
    {
    }

    public function show(Dossier $dossier): EncodableResponse
    {
        $validationResult = $this->dossierService->validateDossier($dossier->diseaseModel, $dossier, null);
        return $this->responseForDossier($dossier, $validationResult);
    }

    public function create(Disease $disease, DiseaseModel $diseaseModel, Request $request, AuthenticationService $authService): EncodableResponse
    {
        $dossier = $this->dossierService->makeDossier($diseaseModel, $authService->getRequiredSelectedOrganisation());
        return $this->handleCreateOrUpdate($diseaseModel, $dossier, $request);
    }

    public function update(Dossier $dossier, Request $request): EncodableResponse
    {
        return $this->handleCreateOrUpdate($dossier->diseaseModel, $dossier, $request);
    }

    public function validateForCreate(Disease $disease, DiseaseModel $diseaseModel, Request $request): EncodableResponse
    {
        return $this->handleValidateForCreateOrUpdate($diseaseModel, null, $request);
    }

    public function validateForUpdate(Dossier $dossier, Request $request): EncodableResponse
    {
        return $this->handleValidateForCreateOrUpdate($dossier->diseaseModel, $dossier, $request);
    }

    private function handleCreateOrUpdate(DiseaseModel $diseaseModel, Dossier $dossier, Request $request): EncodableResponse
    {
        $data = $request->json() instanceof ParameterBag ? $request->json()->all() : [];

        $validationResult = $this->dossierService->validateDossier($diseaseModel, $dossier, $data);
        if (!$validationResult->isValid()) {
            return new EncodableResponse(['validationResult' => $validationResult], 400);
        }

        $isNew = !$dossier->exists;
        $dossier = $this->dossierService->decodeDossier($dossier, $data);
        $this->dossierService->saveDossier($dossier);

        return $this->responseForDossier($dossier, $validationResult, $isNew ? 201 : 200);
    }

    private function handleValidateForCreateOrUpdate(DiseaseModel $diseaseModel, ?Dossier $dossier, Request $request): EncodableResponse
    {
        $data = $request->json() instanceof ParameterBag ? $request->json()->all() : [];
        $validationResult = $this->dossierService->validateDossier($diseaseModel, $dossier, $data);
        return new EncodableResponse(['validationResult' => $validationResult], $validationResult->isValid() ? 200 : 400);
    }

    private function responseForDossier(Dossier $dossier, ValidationResult $validationResult, int $status = 200): EncodableResponse
    {
        $data = $this->dossierService->encodeDossier($dossier);

        $links = [
            'self' => ['href' => route('api-dossier-show', ['dossier' => $dossier])],
            'form' => [
                'href' => route(
                    'api-disease-model-show-form',
                    ['disease' => $dossier->diseaseModel->disease->id, 'diseaseModelVersion' => $dossier->diseaseModel->version, 'entityName' => Entity::Dossier->value],
                )],
            'update' => ['href' => route('api-dossier-update', ['dossier' => $dossier]), 'method' => 'PUT'],
        ];

        return new EncodableResponse(array_filter(['data' => $data, 'validationResult' => $validationResult, 'links' => $links]), $status);
    }
}
