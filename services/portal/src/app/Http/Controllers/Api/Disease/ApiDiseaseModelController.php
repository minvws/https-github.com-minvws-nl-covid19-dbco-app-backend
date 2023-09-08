<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Disease;

use App\Http\Controllers\Api\ApiController;
use App\Http\Responses\Api\Disease\DiseaseModelIndexDecorator;
use App\Http\Responses\EncodableResponse;
use App\Http\Responses\EncodableResponseBuilder;
use App\Models\Disease\Disease;
use App\Models\Disease\DiseaseModel;
use App\Models\Disease\Entity;
use App\Schema\Validation\ValidationResult;
use App\Services\Disease\DiseaseFormService;
use App\Services\Disease\DiseaseModelService;
use App\Services\Disease\InvalidOperationException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\ParameterBag;

use function abort_if;
use function response;

class ApiDiseaseModelController extends ApiController
{
    public function __construct(private readonly DiseaseModelService $diseaseModelService)
    {
    }

    public function index(Disease $disease, DiseaseModelIndexDecorator $decorator): EncodableResponse
    {
        $diseaseModels = $this->diseaseModelService->getAllDiseaseModels($disease);
        return EncodableResponseBuilder::create($diseaseModels)->registerDecorator(DiseaseModel::class, $decorator)->build();
    }

    public function show(DiseaseModel $diseaseModel): EncodableResponse
    {
        $validationResult = $this->diseaseModelService->validateDiseaseModel($diseaseModel, null);
        return $this->responseForDiseaseModel($diseaseModel, $validationResult);
    }

    public function showForm(Disease $disease, DiseaseModel $diseaseModel, string $entityName, DiseaseFormService $diseaseFormService): EncodableResponse
    {
        $entity = Entity::tryFrom($entityName);
        abort_if($entity === null, 404);
        $form = $diseaseFormService->getDiseaseModelForm($diseaseModel, $entity);
        abort_if($form === null, 404);
        return EncodableResponseBuilder::create($form)->build();
    }

    public function create(Disease $disease, Request $request): EncodableResponse
    {
        $diseaseModel = $this->diseaseModelService->makeDiseaseModel($disease);
        return $this->handleCreateOrUpdate($diseaseModel, $request);
    }

    public function update(DiseaseModel $diseaseModel, Request $request): EncodableResponse
    {
        return $this->handleCreateOrUpdate($diseaseModel, $request);
    }

    public function validateForCreate(Disease $disease, Request $request): EncodableResponse
    {
        return $this->handleValidateForCreateOrUpdate(null, $request);
    }

    public function validateForUpdate(DiseaseModel $diseaseModel, Request $request): EncodableResponse
    {
        return $this->handleValidateForCreateOrUpdate($diseaseModel, $request);
    }

    public function delete(DiseaseModel $diseaseModel): Response
    {
        try {
            $this->diseaseModelService->deleteDiseaseModel($diseaseModel);
            return response(status: 204);
        } catch (InvalidOperationException $e) {
            return EncodableResponseBuilder::create(['error' => $e], 400)->build();
        }
    }

    public function publish(DiseaseModel $diseaseModel): Response
    {
        try {
            $this->diseaseModelService->publishDiseaseModel($diseaseModel);
            return response(status: 204);
        } catch (InvalidOperationException $e) {
            return EncodableResponseBuilder::create(['error' => $e], 400)->build();
        }
    }

    public function archive(DiseaseModel $diseaseModel): Response
    {
        try {
            $this->diseaseModelService->archiveDiseaseModel($diseaseModel);
            return response(status: 204);
        } catch (InvalidOperationException $e) {
            return EncodableResponseBuilder::create(['error' => $e], 400)->build();
        }
    }

    public function clone(DiseaseModel $diseaseModel): Response
    {
        try {
            $clone = $this->diseaseModelService->cloneDiseaseModel($diseaseModel);
            $validationResult = $this->diseaseModelService->validateDiseaseModel($diseaseModel, null);
            return $this->responseForDiseaseModel($clone, $validationResult, 201);
        } catch (InvalidOperationException $e) {
            return EncodableResponseBuilder::create(['error' => $e], 400)->build();
        }
    }

    private function handleCreateOrUpdate(DiseaseModel $diseaseModel, Request $request): EncodableResponse
    {
        $data = $request->json() instanceof ParameterBag ? $request->json()->all() : [];

        $validationResult = $this->diseaseModelService->validateDiseaseModel($diseaseModel, $data);
        if (!$validationResult->isValid()) {
            return EncodableResponseBuilder::create(['validationResult' => $validationResult], 400)->build();
        }

        $isNew = !$diseaseModel->exists;
        $diseaseModel = $this->diseaseModelService->decodeDiseaseModel($diseaseModel, $data);

        try {
            $this->diseaseModelService->saveDiseaseModel($diseaseModel);
        } catch (InvalidOperationException $e) {
            return EncodableResponseBuilder::create(['error' => $e], 400)->build();
        }

        return $this->responseForDiseaseModel($diseaseModel, $validationResult, $isNew ? 201 : 200);
    }

    private function handleValidateForCreateOrUpdate(?DiseaseModel $diseaseModel, Request $request): EncodableResponse
    {
        $data = $request->json() instanceof ParameterBag ? $request->json()->all() : [];
        $validationResult = $this->diseaseModelService->validateDiseaseModel($diseaseModel, $data);
        return EncodableResponseBuilder::create(
            ['validationResult' => $validationResult],
            $validationResult->isValid() ? 200 : 400,
        )->build();
    }

    private function responseForDiseaseModel(DiseaseModel $diseaseModel, ValidationResult $validationResult, int $status = 200): EncodableResponse
    {
        $data = $this->diseaseModelService->encodeDiseaseModel($diseaseModel);
        return EncodableResponseBuilder::create(['data' => $data, 'validationResult' => $validationResult], $status)->build();
    }
}
