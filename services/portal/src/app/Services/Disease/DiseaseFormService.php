<?php

declare(strict_types=1);

namespace App\Services\Disease;

use App\Models\Disease\DiseaseModel;
use App\Models\Disease\DiseaseModelUI;
use App\Models\Disease\Entity;
use App\Models\Disease\VersionStatus;
use App\Models\JSONForms\Form;
use App\Repositories\Disease\DiseaseModelUIRepository;
use App\Schema\JSONSchema\JSONSchemaEncoder;
use App\Services\Disease\HasMany\HasManyEncoder;
use App\Services\Disease\HasMany\HasManyType;
use stdClass;

use function assert;
use function is_object;
use function json_decode;

class DiseaseFormService
{
    public function __construct(
        private readonly DiseaseModelUIRepository $diseaseModelUIRepository,
        private readonly DiseaseSchemaService $diseaseSchemaService,
    )
    {
    }

    public function getDiseaseModelForm(DiseaseModel $model, Entity $entity): ?Form
    {
        $ui = $this->diseaseModelUIRepository->firstDiseaseModelUIWithStatus($model, VersionStatus::Published);

        if ($ui === null && $model->status === VersionStatus::Draft) {
            // fallback to draft ui if there is no published ui and the model itself also has a draft status
            $ui = $this->diseaseModelUIRepository->firstDiseaseModelUIWithStatus($model, VersionStatus::Draft);
        }

        if ($ui === null) {
            return null;
        }

        return new Form(
            $this->getDataSchema($model, $entity),
            $this->getUISchema($ui, $entity),
            $this->getTranslations($ui),
        );
    }

    public function getDiseaseModelUIForm(DiseaseModelUI $ui, Entity $entity): Form
    {
        return new Form(
            $this->getDataSchema($ui->diseaseModel, $entity),
            $this->getUISchema($ui, $entity),
            $this->getTranslations($ui),
        );
    }

    private function getDataSchema(DiseaseModel $model, Entity $entity): object
    {
        $schema = match ($entity) {
            Entity::Dossier => $this->diseaseSchemaService->getDossierSchema($model),
            Entity::Contact => $this->diseaseSchemaService->getContactSchema($model),
            Entity::Event => $this->diseaseSchemaService->getEventSchema($model)
        };

        $encoder = new JSONSchemaEncoder();
        $encoder->registerTypeEncoder(HasManyType::class, new HasManyEncoder());
        $jsonSchema = $encoder->encode($schema);

        // phpcs:ignore Generic.Commenting.Todo.TaskFound -- baseline
        // ugly workaround to add the data level to our schemas, TODO: revisit at a later time
        $result = new stdClass();
        $result->{'type'} = 'object';
        $result->{'properties'} = new stdClass();
        $result->{'properties'}->{'data'} = $jsonSchema;
        if (isset($jsonSchema->{'$defs'})) {
            $result->{'$defs'} = $jsonSchema->{'$defs'};
            unset($jsonSchema->{'$defs'});
        }
        return $result;
    }

    private function getUISchema(DiseaseModelUI $ui, Entity $entity): object
    {
        $json = match ($entity) {
            Entity::Dossier => $ui->dossier_schema,
            Entity::Contact => $ui->contact_schema,
            Entity::Event => $ui->event_schema
        };

        $schema = json_decode($json);
        assert(is_object($schema));
        return $schema;
    }

    private function getTranslations(DiseaseModelUI $ui): object
    {
        if ($ui->translations === null) {
            return new stdClass();
        }

        $translations = json_decode($ui->translations);
        assert(is_object($translations));
        return $translations;
    }
}
