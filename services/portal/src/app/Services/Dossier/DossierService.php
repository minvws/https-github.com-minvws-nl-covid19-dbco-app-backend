<?php

declare(strict_types=1);

namespace App\Services\Dossier;

use App\Models\Disease\DiseaseModel;
use App\Models\Dossier\Dossier;
use App\Models\Eloquent\EloquentOrganisation;
use App\Repositories\Dossier\DossierRepository;
use App\Schema\Validation\ValidationResult;
use App\Services\BcoNumber\BcoNumberService;
use App\Services\Disease\DiseaseSchemaService;
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

class DossierService
{
    public function __construct(private readonly DossierRepository $dossierRepository, private readonly DiseaseSchemaService $diseaseSchemaService, private readonly BcoNumberService $bcoNumberService)
    {
    }

    public function getDossier(string|int $id): ?Dossier
    {
        $filteredId = filter_var($id, FILTER_VALIDATE_INT);
        if (!is_int($filteredId)) {
            return null;
        }

        return $this->dossierRepository->getDossier($filteredId);
    }

    public function makeDossier(DiseaseModel $diseaseModel, EloquentOrganisation $organisation): Dossier
    {
        return $this->dossierRepository->makeDossier($diseaseModel, $organisation);
    }

    public function validateDossier(DiseaseModel $diseaseModel, ?Dossier $dossier, ?array $data): ValidationResult
    {
        $schemaVersion = $dossier?->getSchemaVersion() ?? $this->diseaseSchemaService->getDossierSchema($diseaseModel)->getCurrentVersion();

        $allData = [];

        if ($dossier?->exists) {
            $encoder = new Encoder();
            $encoder->getContext()->setMode(EncodingContext::MODE_OUTPUT);
            $encoder->getContext()->setUseAssociativeArraysForObjects(true);
            $encoder->getContext()->registerDecorator(Dossier::class, $schemaVersion->getEncodableDecorator());
            $allData = $encoder->encode($dossier);
            assert(is_array($allData));
        }

        $allData = $data !== null ? array_replace_recursive($allData, $data) : $allData;

        return $schemaVersion->validate($allData);
    }

    public function encodeDossier(Dossier $dossier): object
    {
        $encoder = new Encoder();
        $encoder->getContext()->setMode(EncodingContext::MODE_OUTPUT);
        $encoder->getContext()->registerDecorator(Dossier::class, $dossier->getSchemaVersion()->getEncodableDecorator());
        $data = $encoder->encode($dossier);
        assert(is_object($data));
        return $data;
    }

    public function decodeDossier(Dossier $dossier, array $data): Dossier
    {
        $decoder = new Decoder();
        $decoder->getContext()->setMode(DecodingContext::MODE_INPUT);
        $decoder->getContext()->registerDecorator(Dossier::class, $dossier->getSchemaVersion()->getDecodableDecorator());
        $decoder->decode($data)->decodeObject(Dossier::class, $dossier);
        return $dossier;
    }

    public function saveDossier(Dossier $dossier): void
    {
        if (!$dossier->exists) {
            $dossier->identifier = $this->bcoNumberService->makeUniqueNumber()->bco_number;
        }

        $this->dossierRepository->saveDossier($dossier);
    }
}
