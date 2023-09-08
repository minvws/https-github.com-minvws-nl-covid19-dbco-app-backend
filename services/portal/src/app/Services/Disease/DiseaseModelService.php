<?php

declare(strict_types=1);

namespace App\Services\Disease;

use App\Models\Disease\Disease;
use App\Models\Disease\DiseaseModel;
use App\Models\Disease\VersionStatus;
use App\Repositories\Disease\DiseaseModelRepository;
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

class DiseaseModelService
{
    private const VERSION_CURRENT = 'current';
    private const VERSION_DRAFT = 'draft';

    public function __construct(private readonly DiseaseModelRepository $diseaseModelRepository)
    {
    }

    public function getAllDiseaseModels(Disease $disease): Collection
    {
        return $this->diseaseModelRepository->getAllDiseaseModels($disease);
    }

    public function getDiseaseModel(string|int $id): ?DiseaseModel
    {
        $filteredId = filter_var($id, FILTER_VALIDATE_INT);
        if (is_int($filteredId)) {
            return $this->diseaseModelRepository->getDiseaseModel($filteredId);
        }

        return null;
    }

    public function getDiseaseModelByVersion(Disease $disease, string|int $version): ?DiseaseModel
    {
        if ($version === self::VERSION_CURRENT) {
            return $this->diseaseModelRepository->firstDiseaseModelWithStatus($disease, VersionStatus::Published);
        }

        if ($version === self::VERSION_DRAFT) {
            return $this->diseaseModelRepository->firstDiseaseModelWithStatus($disease, VersionStatus::Draft);
        }

        $filteredVersion = filter_var($version, FILTER_VALIDATE_INT);
        if (is_int($filteredVersion)) {
            return $this->diseaseModelRepository->firstDiseaseModelWithVersion($disease, $filteredVersion);
        }

        return null;
    }

    public function makeDiseaseModel(Disease $disease): DiseaseModel
    {
        return $this->diseaseModelRepository->makeDiseaseModel($disease);
    }

    public function validateDiseaseModel(?DiseaseModel $diseaseModel, ?array $data): ValidationResult
    {
        $schemaVersion = $diseaseModel?->getSchemaVersion() ?? DiseaseModel::getSchema()->getCurrentVersion();

        $allData = [];

        if ($diseaseModel?->exists) {
            $encoder = new Encoder();
            $encoder->getContext()->setMode(EncodingContext::MODE_OUTPUT);
            $encoder->getContext()->setUseAssociativeArraysForObjects(true);
            $encoder->getContext()->registerDecorator(DiseaseModel::class, $schemaVersion->getEncodableDecorator());
            $allData = $encoder->encode($diseaseModel);
            assert(is_array($allData));
        }

        $allData = $data !== null ? array_replace_recursive($allData, $data) : $allData;

        return $schemaVersion->validate($allData);
    }

    public function encodeDiseaseModel(DiseaseModel $diseaseModel): object
    {
        $encoder = new Encoder();
        $encoder->getContext()->setMode(EncodingContext::MODE_OUTPUT);
        $encoder->getContext()->registerDecorator(DiseaseModel::class, $diseaseModel->getSchemaVersion()->getEncodableDecorator());
        $data = $encoder->encode($diseaseModel);
        assert(is_object($data));
        return $data;
    }

    public function decodeDiseaseModel(DiseaseModel $diseaseModel, array $data): DiseaseModel
    {
        $decoder = new Decoder();
        $decoder->getContext()->setMode(DecodingContext::MODE_INPUT);
        $decoder->getContext()->registerDecorator(DiseaseModel::class, $diseaseModel->getSchemaVersion()->getDecodableDecorator());
        return $decoder->decode($data)->decodeObject(DiseaseModel::class, $diseaseModel);
    }

    /**
     * @throws InvalidOperationException
     */
    public function saveDiseaseModel(DiseaseModel $diseaseModel): void
    {
        if ($diseaseModel->exists && $diseaseModel->status !== VersionStatus::Draft) {
            throw new InvalidOperationException('Only draft models can be modified!');
        }

        if (!$diseaseModel->exists && $diseaseModel->status === VersionStatus::Draft) {
            $this->validateNoDraftDiseaseModelExists($diseaseModel->disease);
        }

        $this->diseaseModelRepository->saveDiseaseModel($diseaseModel);
    }

    /**
     * @throws InvalidOperationException
     */
    public function deleteDiseaseModel(DiseaseModel $diseaseModel): void
    {
        if ($diseaseModel->status !== VersionStatus::Draft) {
            throw new InvalidOperationException('Only draft models can be deleted!');
        }

        $this->diseaseModelRepository->deleteDiseaseModel($diseaseModel);
    }

    /**
     * @throws InvalidOperationException
     */
    public function publishDiseaseModel(DiseaseModel $diseaseModel): void
    {
        if ($diseaseModel->status !== VersionStatus::Draft) {
            throw new InvalidOperationException('Only draft models can be published!');
        }

        $this->diseaseModelRepository->publishDiseaseModel($diseaseModel);
    }

    /**
     * @throws InvalidOperationException
     */
    public function archiveDiseaseModel(DiseaseModel $diseaseModel): void
    {
        if ($diseaseModel->status !== VersionStatus::Published) {
            throw new InvalidOperationException('Only published models can be archived!');
        }

        $this->diseaseModelRepository->archiveDiseaseModel($diseaseModel);
    }

    /**
     * @throws InvalidOperationException
     */
    public function cloneDiseaseModel(DiseaseModel $diseaseModel): DiseaseModel
    {
        if ($diseaseModel->status === VersionStatus::Draft) {
            throw new InvalidOperationException('Only published/archived models can be cloned!');
        }

        $this->validateNoDraftDiseaseModelExists($diseaseModel->disease);

        $clone = $this->makeDiseaseModel($diseaseModel->disease);
        $clone->dossier_schema = $diseaseModel->dossier_schema;
        $clone->contact_schema = $diseaseModel->contact_schema;
        $clone->event_schema = $diseaseModel->event_schema;
        $clone->shared_defs = $diseaseModel->shared_defs;
        $this->saveDiseaseModel($clone);

        return $clone;
    }

    /**
     * @throws InvalidOperationException
     */
    private function validateNoDraftDiseaseModelExists(Disease $disease): void
    {
        $draft = $this->diseaseModelRepository->firstDiseaseModelWithStatus($disease, VersionStatus::Draft);
        if ($draft !== null) {
            throw new InvalidOperationException('A draft model already exists!');
        }
    }
}
