<?php

declare(strict_types=1);

namespace App\Services\Disease;

use App\Models\Disease\Disease;
use App\Repositories\Disease\DiseaseRepository;
use App\Schema\Validation\ValidationResult;
use Illuminate\Support\Collection;
use MinVWS\Codable\Decoder;
use MinVWS\Codable\DecodingContext;
use MinVWS\Codable\Encoder;
use MinVWS\Codable\EncodingContext;

use function array_replace_recursive;
use function assert;
use function filter_var;
use function is_array;
use function is_int;
use function is_object;

use const FILTER_VALIDATE_INT;

class DiseaseService
{
    public function __construct(private readonly DiseaseRepository $diseaseRepository)
    {
    }

    public function getAllDiseases(): Collection
    {
        return $this->diseaseRepository->getAllDiseases();
    }

    public function getActiveDiseases(): Collection
    {
        return $this->diseaseRepository->getActiveDiseases();
    }

    public function getDisease(string $id): ?Disease
    {
        $filteredId = filter_var($id, FILTER_VALIDATE_INT);
        if (!is_int($filteredId)) {
            return null;
        }

        return $this->diseaseRepository->getDisease($filteredId);
    }

    public function makeDisease(): Disease
    {
        return $this->diseaseRepository->makeDisease();
    }

    public function validateDisease(?Disease $disease, ?array $data): ValidationResult
    {
        $schemaVersion = $disease?->getSchemaVersion() ?? Disease::getSchema()->getCurrentVersion();

        $allData = [];

        if ($disease?->exists) {
            $encoder = new Encoder();
            $encoder->getContext()->setMode(EncodingContext::MODE_OUTPUT);
            $encoder->getContext()->setUseAssociativeArraysForObjects(true);
            $encoder->getContext()->registerDecorator(Disease::class, $schemaVersion->getEncodableDecorator());
            $allData = $encoder->encode($disease);
            assert(is_array($allData));
        }

        $allData = $data !== null ? array_replace_recursive($allData, $data) : $allData;

        return $schemaVersion->validate($allData);
    }

    public function encodeDisease(Disease $disease): object
    {
        $encoder = new Encoder();
        $encoder->getContext()->setMode(EncodingContext::MODE_OUTPUT);
        $encoder->getContext()->registerDecorator(Disease::class, $disease->getSchemaVersion()->getEncodableDecorator());
        $data = $encoder->encode($disease);
        assert(is_object($data));
        return $data;
    }

    public function decodeDisease(Disease $disease, array $data): Disease
    {
        $decoder = new Decoder();
        $decoder->getContext()->setMode(DecodingContext::MODE_INPUT);
        $decoder->getContext()->registerDecorator(Disease::class, $disease->getSchemaVersion()->getDecodableDecorator());
        return $decoder->decode($data)->decodeObject(Disease::class, $disease);
    }

    /**
     * @throws InvalidOperationException
     */
    public function saveDisease(Disease $disease): void
    {
        $this->diseaseRepository->saveDisease($disease);
    }

    /**
     * @throws InvalidOperationException
     */
    public function deleteDisease(Disease $disease): void
    {
        if ($disease->models->count() > 0) {
            throw new InvalidOperationException('Only diseases without models can be deleted!');
        }

        $this->diseaseRepository->deleteDisease($disease);
    }
}
