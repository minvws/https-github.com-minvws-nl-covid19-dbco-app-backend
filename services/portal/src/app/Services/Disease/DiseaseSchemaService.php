<?php

declare(strict_types=1);

namespace App\Services\Disease;

use App\Models\Disease\DiseaseModel;
use App\Models\Dossier\Contact;
use App\Models\Dossier\ContactFragment;
use App\Models\Dossier\Dossier;
use App\Models\Dossier\DossierFragment;
use App\Models\Dossier\Event;
use App\Models\Dossier\EventFragment;
use App\Models\Dossier\FragmentModel;
use App\Schema\Entity;
use App\Schema\JSONSchema\JSONSchemaDecoder;
use App\Schema\Schema;
use App\Schema\Types\IntType;
use App\Schema\Types\SchemaType;
use App\Schema\Types\StringType;
use App\Services\Disease\HasMany\HasManyDecoder;
use App\Services\Disease\HasMany\HasManyType;
use MinVWS\Codable\DecodingContainer;
use MinVWS\Codable\EncodingContainer;
use MinVWS\Codable\EncodingContext;
use stdClass;

use function array_merge;
use function assert;
use function is_array;
use function is_string;
use function json_decode;
use function json_encode;

class DiseaseSchemaService
{
    private array $cache = [];

    private function mergeDefs(string $schemaJson, ?string $sharedDefsJson): string
    {
        $schema = json_decode($schemaJson);
        assert($schema instanceof stdClass);
        $sharedDefs = !empty($sharedDefsJson) ? json_decode($sharedDefsJson) : [];
        assert(is_array($sharedDefs));
        $schema->{'$defs'} = array_merge($sharedDefs, $schema->{'$defs'} ?? []);
        $json = json_encode($schema);
        assert(is_string($json));
        return $json;
    }

    /**
     * @param class-string<FragmentModel> $fragmentClass
     */
    private function loadSchema(Schema $schema, string $schemaJson, ?string $sharedDefsJson, string $fragmentClass, array $hasManySchemas = []): Schema
    {
        $json = $this->mergeDefs($schemaJson, $sharedDefsJson);
        $decoder = new JSONSchemaDecoder();
        $decoder->setSchemaFactory(fn (DecodingContainer $c) => $this->createSchemaForObjectInContainer($schema, $c, $fragmentClass));
        $decoder->registerTypeDecoder('hasMany', new HasManyDecoder($hasManySchemas));
        $schemaType = $decoder->decode($json);
        assert($schemaType instanceof SchemaType);
        return $schemaType->getSchema();
    }

    /**
     * @param class-string<FragmentModel> $fragmentClass
     */
    private function createSchemaForObjectInContainer(Schema $baseSchema, DecodingContainer $container, string $fragmentClass): Schema
    {
        if ($container->getParent() === null) {
            return $baseSchema;
        }

        if ($container->getParent()->getParent()?->getParent() === null) {
            return new Schema($fragmentClass, false);
        }

        return new Schema(Entity::class, false);
    }

    private function createDossierSchemaBase(): Schema
    {
        $schema = new Schema(Dossier::class, false);
        $schema->add(IntType::createField('id'));
        $schema->add(StringType::createField('identifier'))
            ->setExcluded()
            ->setIncludedInEncode(true, EncodingContext::MODE_OUTPUT);

        $schema->add(DiseaseModel::getSchema()->getCurrentVersion()->createField('diseaseModel'))
            ->setExcluded()
            ->setIncludedInEncode(true, EncodingContext::MODE_OUTPUT)
            ->setEncoder(
                fn (EncodingContainer $container, DiseaseModel $diseaseModel) => $this->encodeDiseaseModel($container, $diseaseModel),
                EncodingContext::MODE_OUTPUT,
            );

        return $schema;
    }

    private function loadDossierSchema(DiseaseModel $diseaseModel): Schema
    {
        return $this->loadSchema(
            $this->createDossierSchemaBase(),
            $diseaseModel->dossier_schema,
            $diseaseModel->shared_defs,
            DossierFragment::class,
            ['contacts' => $this->loadContactSchema($diseaseModel), 'events' => $this->loadEventSchema($diseaseModel)],
        );
    }

    private function encodeDiseaseModel(EncodingContainer $container, DiseaseModel $diseaseModel): void
    {
        $container->id = $diseaseModel->id;
        $container->disease->id = $diseaseModel->disease->id;
        $container->disease->code = $diseaseModel->disease->code;
        $container->disease->name = $diseaseModel->disease->name;
        $container->version = $diseaseModel->version;
        $container->status = $diseaseModel->status;
    }

    private function createContactSchemaBase(): Schema
    {
        $schema = new Schema(Contact::class, false);
        $schema->add(IntType::createField('id'))->addToView(HasManyType::VIEW_LIST);
        $schema->add(StringType::createField('identifier'))
            ->addToView(HasManyType::VIEW_LIST)
            ->setExcluded()
            ->setIncludedInEncode(true, EncodingContext::MODE_OUTPUT);

        return $schema;
    }

    private function loadContactSchema(DiseaseModel $diseaseModel): Schema
    {
        return $this->loadSchema(
            $this->createContactSchemaBase(),
            $diseaseModel->contact_schema,
            $diseaseModel->shared_defs,
            ContactFragment::class,
        );
    }

    private function createEventSchemaBase(): Schema
    {
        return new Schema(Event::class, false);
    }

    private function loadEventSchema(DiseaseModel $diseaseModel): Schema
    {
        return $this->loadSchema(
            $this->createEventSchemaBase(),
            $diseaseModel->event_schema,
            $diseaseModel->shared_defs,
            EventFragment::class,
        );
    }

    public function getDossierSchema(DiseaseModel $diseaseModel): Schema
    {
        if (!isset($this->cache[$diseaseModel->id])) {
            $this->cache[$diseaseModel->id] = $this->loadDossierSchema($diseaseModel);
        }

        return $this->cache[$diseaseModel->id];
    }

    public function getContactSchema(DiseaseModel $diseaseModel): Schema
    {
        $contactSchema = $this->getDossierSchema($diseaseModel);
        return $contactSchema->getCurrentVersion()->getExpectedField('contacts')->getExpectedType(HasManyType::class)->schema;
    }

    public function getEventSchema(DiseaseModel $diseaseModel): Schema
    {
        $eventSchema = $this->getDossierSchema($diseaseModel);
        return $eventSchema->getCurrentVersion()->getExpectedField('events')->getExpectedType(HasManyType::class)->schema;
    }
}
