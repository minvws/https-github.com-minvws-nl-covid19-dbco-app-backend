<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Disease;

use App\Http\Controllers\Api\ApiController;
use App\Http\Responses\Api\Disease\DiseaseIndexDecorator;
use App\Http\Responses\EncodableResponse;
use App\Http\Responses\EncodableResponseBuilder;
use App\Models\Disease\Disease;
use App\Schema\Validation\ValidationResult;
use App\Services\Disease\DiseaseService;
use App\Services\Disease\InvalidOperationException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\ParameterBag;

use function response;

class ApiDiseaseController extends ApiController
{
    public function __construct(private readonly DiseaseService $diseaseService)
    {
    }

    public function index(DiseaseIndexDecorator $decorator): EncodableResponse
    {
        $diseases = $this->diseaseService->getAllDiseases();
        return EncodableResponseBuilder::create($diseases)->registerDecorator(Disease::class, $decorator)->build();
    }

    public function active(DiseaseIndexDecorator $decorator): EncodableResponse
    {
        $diseases = $this->diseaseService->getActiveDiseases();
        return EncodableResponseBuilder::create($diseases)->registerDecorator(
            Disease::class,
            Disease::getSchema()->getCurrentVersion()->getEncodableDecorator(),
        )->build();
    }

    public function show(Disease $disease): EncodableResponse
    {
        $validationResult = $this->diseaseService->validateDisease($disease, null);
        return $this->responseForDisease($disease, $validationResult);
    }

    public function create(Request $request): EncodableResponse
    {
        $disease = $this->diseaseService->makeDisease();
        return $this->handleCreateOrUpdate($disease, $request);
    }

    public function update(Disease $disease, Request $request): EncodableResponse
    {
        return $this->handleCreateOrUpdate($disease, $request);
    }

    public function validateForCreate(Request $request): EncodableResponse
    {
        return $this->handleValidateForCreateOrUpdate(null, $request);
    }

    public function validateForUpdate(Disease $disease, Request $request): EncodableResponse
    {
        return $this->handleValidateForCreateOrUpdate($disease, $request);
    }

    public function delete(Disease $disease): Response
    {
        try {
            $this->diseaseService->deleteDisease($disease);
            return response(status: 204);
        } catch (InvalidOperationException $e) {
            return EncodableResponseBuilder::create(['error' => $e], 400)->build();
        }
    }

    private function handleCreateOrUpdate(Disease $disease, Request $request): EncodableResponse
    {
        $data = $request->json() instanceof ParameterBag ? $request->json()->all() : [];

        $validationResult = $this->diseaseService->validateDisease($disease, $data);
        if (!$validationResult->isValid()) {
            return EncodableResponseBuilder::create(['validationResult' => $validationResult], 400)->build();
        }

        $isNew = !$disease->exists;
        $disease = $this->diseaseService->decodeDisease($disease, $data);

        try {
            $this->diseaseService->saveDisease($disease);
        } catch (InvalidOperationException $e) {
            return EncodableResponseBuilder::create(['error' => $e], 400)->build();
        }

        return $this->responseForDisease($disease, $validationResult, $isNew ? 201 : 200);
    }

    private function handleValidateForCreateOrUpdate(?Disease $disease, Request $request): EncodableResponse
    {
        $data = $request->json() instanceof ParameterBag ? $request->json()->all() : [];
        $validationResult = $this->diseaseService->validateDisease($disease, $data);
        return EncodableResponseBuilder::create(
            ['validationResult' => $validationResult],
            $validationResult->isValid() ? 200 : 400,
        )->build();
    }

    private function responseForDisease(Disease $disease, ValidationResult $validationResult, int $status = 200): EncodableResponse
    {
        $data = $this->diseaseService->encodeDisease($disease);
        return EncodableResponseBuilder::create(['data' => $data, 'validationResult' => $validationResult], $status)->build();
    }
}
