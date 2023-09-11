<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Disease;

use App\Http\Controllers\Api\ApiController;
use App\Http\Responses\Api\Disease\DiseaseModelUIIndexDecorator;
use App\Http\Responses\EncodableResponse;
use App\Http\Responses\EncodableResponseBuilder;
use App\Models\Disease\Disease;
use App\Models\Disease\DiseaseModel;
use App\Models\Disease\DiseaseModelUI;
use App\Models\Disease\Entity;
use App\Schema\Validation\ValidationResult;
use App\Services\Disease\DiseaseFormService;
use App\Services\Disease\DiseaseModelUIService;
use App\Services\Disease\InvalidOperationException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\ParameterBag;

use function abort_if;
use function response;

class ApiDiseaseModelUIController extends ApiController
{
    public function __construct(private readonly DiseaseModelUIService $diseaseModelUIService)
    {
    }

    public function index(DiseaseModel $diseaseModel, DiseaseModelUIIndexDecorator $decorator): EncodableResponse
    {
        $diseaseModels = $this->diseaseModelUIService->getAllDiseaseModelUIs($diseaseModel);
        return EncodableResponseBuilder::create($diseaseModels)->registerDecorator(DiseaseModel::class, $decorator)->build();
    }

    public function show(DiseaseModelUI $diseaseModelUI): EncodableResponse
    {
        $validationResult = $this->diseaseModelUIService->validateDiseaseModelUI($diseaseModelUI, null);
        return $this->responseForDiseaseModelUI($diseaseModelUI, $validationResult);
    }

    public function showForm(Disease $disease, DiseaseModel $diseaseModel, DiseaseModelUI $diseaseModelUI, string $entityName, DiseaseFormService $diseaseFormService): EncodableResponse
    {
        $entity = Entity::tryFrom($entityName);
        abort_if($entity === null, 404);
        $form = $diseaseFormService->getDiseaseModelUIForm($diseaseModelUI, $entity);
        return EncodableResponseBuilder::create($form)->build();
    }

    public function create(DiseaseModel $diseaseModel, Request $request): EncodableResponse
    {
        $diseaseModelUI = $this->diseaseModelUIService->makeDiseaseModelUI($diseaseModel);
        return $this->handleCreateOrUpdate($diseaseModelUI, $request);
    }

    public function update(DiseaseModelUI $diseaseModelUI, Request $request): EncodableResponse
    {
        return $this->handleCreateOrUpdate($diseaseModelUI, $request);
    }

    public function validateForCreate(DiseaseModel $diseaseModel, Request $request): EncodableResponse
    {
        return $this->handleValidateForCreateOrUpdate(null, $request);
    }

    public function validateForUpdate(DiseaseModelUI $diseaseModelUI, Request $request): EncodableResponse
    {
        return $this->handleValidateForCreateOrUpdate($diseaseModelUI, $request);
    }

    public function delete(DiseaseModelUI $diseaseModelUI): Response
    {
        try {
            $this->diseaseModelUIService->deleteDiseaseModelUI($diseaseModelUI);
            return response(status: 204);
        } catch (InvalidOperationException $e) {
            return EncodableResponseBuilder::create(['error' => $e], 400)->build();
        }
    }

    public function publish(DiseaseModelUI $diseaseModelUI): Response
    {
        try {
            $this->diseaseModelUIService->publishDiseaseModelUI($diseaseModelUI);
            return response(status: 204);
        } catch (InvalidOperationException $e) {
            return EncodableResponseBuilder::create(['error' => $e], 400)->build();
        }
    }

    public function archive(DiseaseModelUI $diseaseModelUI): Response
    {
        try {
            $this->diseaseModelUIService->archiveDiseaseModelUI($diseaseModelUI);
            return response(status: 204);
        } catch (InvalidOperationException $e) {
            return EncodableResponseBuilder::create(['error' => $e], 400)->build();
        }
    }

    public function clone(DiseaseModelUI $diseaseModelUI): Response
    {
        try {
            $clone = $this->diseaseModelUIService->cloneDiseaseModelUI($diseaseModelUI);
            $validationResult = $this->diseaseModelUIService->validateDiseaseModelUI($diseaseModelUI, null);
            return $this->responseForDiseaseModelUI($clone, $validationResult, 201);
        } catch (InvalidOperationException $e) {
            return EncodableResponseBuilder::create(['error' => $e], 400)->build();
        }
    }

    private function handleCreateOrUpdate(DiseaseModelUI $diseaseModelUI, Request $request): EncodableResponse
    {
        $data = $request->json() instanceof ParameterBag ? $request->json()->all() : [];

        $validationResult = $this->diseaseModelUIService->validateDiseaseModelUI($diseaseModelUI, $data);
        if (!$validationResult->isValid()) {
            return EncodableResponseBuilder::create(['validationResult' => $validationResult], 400)->build();
        }

        $isNew = !$diseaseModelUI->exists;
        $diseaseModelUI = $this->diseaseModelUIService->decodeDiseaseModelUI($diseaseModelUI, $data);

        try {
            $this->diseaseModelUIService->saveDiseaseModelUI($diseaseModelUI);
        } catch (InvalidOperationException $e) {
            return EncodableResponseBuilder::create(['error' => $e], 400)->build();
        }

        return $this->responseForDiseaseModelUI($diseaseModelUI, $validationResult, $isNew ? 201 : 200);
    }

    private function handleValidateForCreateOrUpdate(?DiseaseModelUI $diseaseModelUI, Request $request): EncodableResponse
    {
        $data = $request->json() instanceof ParameterBag ? $request->json()->all() : [];
        $validationResult = $this->diseaseModelUIService->validateDiseaseModelUI($diseaseModelUI, $data);
        return EncodableResponseBuilder::create(
            ['validationResult' => $validationResult],
            $validationResult->isValid() ? 200 : 400,
        )->build();
    }

    private function responseForDiseaseModelUI(DiseaseModelUI $diseaseModelUI, ValidationResult $validationResult, int $status = 200): EncodableResponse
    {
        $data = $this->diseaseModelUIService->encodeDiseaseModelUI($diseaseModelUI);
        return EncodableResponseBuilder::create(['data' => $data, 'validationResult' => $validationResult], $status)->build();
    }
}
