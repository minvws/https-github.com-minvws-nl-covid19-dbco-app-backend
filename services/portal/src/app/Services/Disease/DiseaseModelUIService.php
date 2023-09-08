<?php

declare(strict_types=1);

namespace App\Services\Disease;

use App\Models\Disease\DiseaseModel;
use App\Models\Disease\DiseaseModelUI;
use App\Models\Disease\VersionStatus;
use App\Repositories\Disease\DiseaseModelUIRepository;
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

class DiseaseModelUIService
{
    private const VERSION_CURRENT = 'current';
    private const VERSION_DRAFT = 'draft';

    public function __construct(private readonly DiseaseModelUIRepository $diseaseModelUIRepository)
    {
    }

    public function getAllDiseaseModelUIs(DiseaseModel $diseaseModel): Collection
    {
        return $this->diseaseModelUIRepository->getAllDiseaseModelUIs($diseaseModel);
    }

    public function getDiseaseModelUI(string|int $id): ?DiseaseModelUI
    {
        $filteredId = filter_var($id, FILTER_VALIDATE_INT);
        if (is_int($filteredId)) {
            return $this->diseaseModelUIRepository->getDiseaseModelUI($filteredId);
        }

        return null;
    }

    public function getDiseaseModelUIByVersion(DiseaseModel $diseaseModel, string|int $version): ?DiseaseModelUI
    {
        if ($version === self::VERSION_CURRENT) {
            return $this->diseaseModelUIRepository->firstDiseaseModelUIWithStatus($diseaseModel, VersionStatus::Published);
        }

        if ($version === self::VERSION_DRAFT) {
            return $this->diseaseModelUIRepository->firstDiseaseModelUIWithStatus($diseaseModel, VersionStatus::Draft);
        }

        $filteredVersion = filter_var($version, FILTER_VALIDATE_INT);
        if (is_int($filteredVersion)) {
            return $this->diseaseModelUIRepository->firstDiseaseModelUIWithVersion($diseaseModel, $filteredVersion);
        }

        return null;
    }

    public function makeDiseaseModelUI(DiseaseModel $diseaseModel): DiseaseModelUI
    {
        return $this->diseaseModelUIRepository->makeDiseaseModelUI($diseaseModel);
    }

    public function validateDiseaseModelUI(?DiseaseModelUI $diseaseModelUI, ?array $data): ValidationResult
    {
        $schemaVersion = $diseaseModelUI?->getSchemaVersion() ?? DiseaseModelUI::getSchema()->getCurrentVersion();

        $allData = [];

        if ($diseaseModelUI?->exists) {
            $encoder = new Encoder();
            $encoder->getContext()->setMode(EncodingContext::MODE_OUTPUT);
            $encoder->getContext()->setUseAssociativeArraysForObjects(true);
            $encoder->getContext()->registerDecorator(DiseaseModelUI::class, $schemaVersion->getEncodableDecorator());
            $allData = $encoder->encode($diseaseModelUI);
            assert(is_array($allData));
        }

        $allData = $data !== null ? array_replace_recursive($allData, $data) : $allData;

        return $schemaVersion->validate($allData);
    }

    public function encodeDiseaseModelUI(DiseaseModelUI $diseaseModelUI): object
    {
        $encoder = new Encoder();
        $encoder->getContext()->setMode(EncodingContext::MODE_OUTPUT);
        $encoder->getContext()->registerDecorator(DiseaseModelUI::class, $diseaseModelUI->getSchemaVersion()->getEncodableDecorator());
        $data = $encoder->encode($diseaseModelUI);
        assert(is_object($data));
        return $data;
    }

    public function decodeDiseaseModelUI(DiseaseModelUI $diseaseModelUI, array $data): DiseaseModelUI
    {
        $decoder = new Decoder();
        $decoder->getContext()->setMode(DecodingContext::MODE_INPUT);
        $decoder->getContext()->registerDecorator(DiseaseModelUI::class, $diseaseModelUI->getSchemaVersion()->getDecodableDecorator());
        return $decoder->decode($data)->decodeObject(DiseaseModelUI::class, $diseaseModelUI);
    }

    /**
     * @throws InvalidOperationException
     */
    public function saveDiseaseModelUI(DiseaseModelUI $diseaseModelUI): void
    {
        if ($diseaseModelUI->exists && $diseaseModelUI->status !== VersionStatus::Draft) {
            throw new InvalidOperationException('Only draft models can be modified!');
        }

        if (!$diseaseModelUI->exists && $diseaseModelUI->status === VersionStatus::Draft) {
            $this->validateNoDraftDiseaseModelUIExists($diseaseModelUI->diseaseModel);
        }

        $this->diseaseModelUIRepository->saveDiseaseModelUI($diseaseModelUI);
    }

    /**
     * @throws InvalidOperationException
     */
    public function deleteDiseaseModelUI(DiseaseModelUI $diseaseModelUI): void
    {
        if ($diseaseModelUI->status !== VersionStatus::Draft) {
            throw new InvalidOperationException('Only draft models can be deleted!');
        }

        $this->diseaseModelUIRepository->deleteDiseaseModelUI($diseaseModelUI);
    }

    /**
     * @throws InvalidOperationException
     */
    public function publishDiseaseModelUI(DiseaseModelUI $diseaseModelUI): void
    {
        if ($diseaseModelUI->status !== VersionStatus::Draft) {
            throw new InvalidOperationException('Only draft models can be published!');
        }

        $this->diseaseModelUIRepository->publishDiseaseModelUI($diseaseModelUI);
    }

    /**
     * @throws InvalidOperationException
     */
    public function archiveDiseaseModelUI(DiseaseModelUI $diseaseModelUI): void
    {
        if ($diseaseModelUI->status !== VersionStatus::Published) {
            throw new InvalidOperationException('Only published models can be archived!');
        }

        $this->diseaseModelUIRepository->archiveDiseaseModelUI($diseaseModelUI);
    }

    /**
     * @throws InvalidOperationException
     */
    public function cloneDiseaseModelUI(DiseaseModelUI $diseaseModelUI): DiseaseModelUI
    {
        if ($diseaseModelUI->status === VersionStatus::Draft) {
            throw new InvalidOperationException('Only published/archived models can be cloned!');
        }

        $this->validateNoDraftDiseaseModelUIExists($diseaseModelUI->diseaseModel);

        $clone = $this->makeDiseaseModelUI($diseaseModelUI->diseaseModel);
        $clone->dossier_schema = $diseaseModelUI->dossier_schema;
        $clone->contact_schema = $diseaseModelUI->contact_schema;
        $clone->event_schema = $diseaseModelUI->event_schema;
        $clone->translations = $diseaseModelUI->translations;
        $this->diseaseModelUIRepository->saveDiseaseModelUI($clone);

        return $clone;
    }

    /**
     * @throws InvalidOperationException
     */
    private function validateNoDraftDiseaseModelUIExists(DiseaseModel $diseaseModel): void
    {
        $draft = $this->diseaseModelUIRepository->firstDiseaseModelUIWithStatus($diseaseModel, VersionStatus::Draft);
        if ($draft !== null) {
            throw new InvalidOperationException('A draft ui already exists!');
        }
    }
}
