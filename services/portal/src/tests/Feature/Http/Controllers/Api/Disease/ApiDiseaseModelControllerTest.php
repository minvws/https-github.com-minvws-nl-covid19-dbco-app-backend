<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api\Disease;

use App\Models\Disease\Disease;
use App\Models\Disease\DiseaseModel;
use App\Models\Disease\DiseaseModelUI;
use App\Models\Disease\Entity;
use App\Models\Disease\VersionStatus;
use Generator;
use Tests\Feature\FeatureTestCase;
use Tests\Helpers\ConfigHelper;

use function base_path;
use function file_get_contents;
use function is_null;
use function json_encode;
use function route;
use function str_starts_with;
use function strlen;
use function substr;
use function trim;

/**
 * @group disease
 */
class ApiDiseaseModelControllerTest extends FeatureTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        ConfigHelper::enableFeatureFlag('diseases_and_dossiers_enabled');
    }

    private function routeDiseaseModelCreate(Disease $disease): string
    {
        return route('api-disease-model-create', ['disease' => $disease]);
    }

    public static function validDataProvider(): array
    {
        $simpleDossierSchema = 'file:tests/fixtures/disease/simple/dossier.schema.json';
        $simpleContactSchema = 'file:tests/fixtures/disease/simple/contact.schema.json';
        $simpleEventSchema = 'file:tests/fixtures/disease/simple/event.schema.json';

        return [
            'minimal' => [
                [
                    'dossierSchema' => '{}',
                    'contactSchema' => '{}',
                    'eventSchema' => '{}',
                ],
            ],
            'minimal-with-shared-defs' => [
                [
                    'dossierSchema' => '{}',
                    'contactSchema' => '{}',
                    'eventSchema' => '{}',
                    'sharedDefs' => '[]',
                ],
            ],
            'simple' => [
                [
                    'dossierSchema' => $simpleDossierSchema,
                    'contactSchema' => $simpleContactSchema,
                    'eventSchema' => $simpleEventSchema,
                ],
            ],
            'simple-with-shared-defs' => [
                [
                    'dossierSchema' => $simpleDossierSchema,
                    'contactSchema' => $simpleContactSchema,
                    'eventSchema' => $simpleEventSchema,
                    'sharedDefs' => '[]',
                ],
            ],
        ];
    }

    /**
     * @dataProvider validDataProvider
     */
    public function testCreate(array $data): void
    {
        foreach ($data as $key => $value) {
            if (str_starts_with($value, 'file:')) {
                $data[$key] = trim(file_get_contents(base_path(substr($value, 5))));
            }
        }

        $user = $this->createUser();
        $disease = Disease::factory()->create();

        $response = $this->be($user)->postJson($this->routeDiseaseModelCreate($disease), $data);
        $this->assertStatus($response, 201);
        $this->assertIsInt($response->json('data.id'));
        $response->assertJsonPath('data.status', 'draft');
        foreach ($data as $key => $value) {
            $response->assertJsonPath('data.' . $key, $value);
        }
        $this->assertNull($response->json('validationResult'));
    }

    public function testCreateShouldFailIfADraftAlreadyExists(): void
    {
        $user = $this->createUser();
        $disease = Disease::factory()->create();
        DiseaseModel::factory()->create(['disease_id' => $disease->id]);

        $data = [
            'dossierSchema' => '{}',
            'contactSchema' => '{}',
            'eventSchema' => '{}',
        ];

        $response = $this->be($user)->postJson($this->routeDiseaseModelCreate($disease), $data);
        $this->assertStatus($response, 400);
    }

    public static function invalidDataProvider(): array
    {
        return [
            [['dossierSchema' => null, 'contactSchema' => null, 'eventSchema' => null]],
            [['dossierSchema' => '{}', 'contactSchema' => '{}', 'eventSchema' => null]],
        ];
    }

    public static function invalidCreateDataProvider(): array
    {
        return [
            [[]],
            [['dossierSchema' => '{}', 'contactSchema' => '{}']],
        ];
    }

    /**
     * @dataProvider invalidCreateDataProvider
     * @dataProvider invalidDataProvider
     */
    public function testInvalidCreate(array $data): void
    {
        $user = $this->createUser();
        $disease = Disease::factory()->create();

        $response = $this->be($user)->postJson($this->routeDiseaseModelCreate($disease), $data);
        $this->assertStatus($response, 400);
        $response->assertJsonMissingPath('data');
        $this->assertNotNull($response->json('validationResult'));
    }

    private function routeDiseaseModelUpdate(DiseaseModel $diseaseModel): string
    {
        return route('api-disease-model-update', ['diseaseModel' => $diseaseModel]);
    }

    public static function validUpdateDataProvider(): array
    {
        return [
            [[]],
            [['dossierSchema' => '{}']],
        ];
    }

    /**
     * @dataProvider validUpdateDataProvider
     * @dataProvider validDataProvider
     */
    public function testUpdate(array $data): void
    {
        $user = $this->createUser();
        $disease = Disease::factory()->create();
        $diseaseModel = DiseaseModel::factory()->create(['disease_id' => $disease->id]);

        $response = $this->be($user)->putJson($this->routeDiseaseModelUpdate($diseaseModel), $data);
        $this->assertStatus($response, 200);
        $response->assertJsonPath('data.id', $diseaseModel->id);
        $response->assertJsonPath('data.status', $diseaseModel->status->value);
        foreach ($data as $key => $value) {
            $response->assertJsonPath('data.' . $key, $value);
        }
        $this->assertNull($response->json('validationResult'));
    }

    public static function invalidUpdateDataProvider(): array
    {
        return [
            [[], VersionStatus::Published, false],
            [[], VersionStatus::Archived, false],
        ];
    }

    /**
     * @dataProvider invalidUpdateDataProvider
     * @dataProvider invalidDataProvider
     */
    public function testInvalidUpdate(array $data, VersionStatus $status = VersionStatus::Draft, bool $expectsValidationResult = true): void
    {
        $user = $this->createUser();
        $disease = Disease::factory()->create();
        $diseaseModel = DiseaseModel::factory()->create(['disease_id' => $disease->id, 'status' => $status]);

        $response = $this->be($user)->putJson($this->routeDiseaseModelUpdate($diseaseModel), $data);
        $this->assertStatus($response, 400);
        $response->assertJsonMissingPath('data');
        if ($expectsValidationResult) {
            $this->assertNotNull($response->json('validationResult'));
        } else {
            $response->assertJsonMissingPath('validationResult');
        }
    }

    public static function deleteProvider(): array
    {
        return [
            [VersionStatus::Draft, 204, false],
            [VersionStatus::Published, 400, true],
            [VersionStatus::Archived, 400, true],
        ];
    }

    /**
     * @dataProvider deleteProvider
     */
    public function testDelete(VersionStatus $status, int $expectedStatus, bool $expectsError): void
    {
        $user = $this->createUser();
        $disease = Disease::factory()->create();
        $diseaseModel = DiseaseModel::factory()->create(['disease_id' => $disease->id, 'status' => $status]);

        $response = $this->be($user)->delete(route('api-disease-model-delete', ['diseaseModel' => $diseaseModel]));
        $this->assertStatus($response, $expectedStatus);

        if ($expectsError) {
            $this->assertNotNull($response->json('error'));
        } else {
            $this->assertEquals(0, strlen($response->getContent()));
        }

        $this->assertEquals($expectsError, !is_null(DiseaseModel::find($diseaseModel->id)));
    }

    public static function publishProvider(): array
    {
        return [
            [VersionStatus::Draft, 204, false],
            [VersionStatus::Published, 400, true],
            [VersionStatus::Archived, 400, true],
        ];
    }

    /**
     * @dataProvider publishProvider
     */
    public function testPublish(VersionStatus $status, int $expectedStatus, bool $expectsError): void
    {
        $user = $this->createUser();
        $disease = Disease::factory()->create();
        $diseaseModel = DiseaseModel::factory()->create(['disease_id' => $disease->id, 'status' => $status]);

        $response = $this->be($user)->patchJson(route('api-disease-model-publish', ['diseaseModel' => $diseaseModel]));
        $this->assertStatus($response, $expectedStatus);

        if ($expectsError) {
            $this->assertNotNull($response->json('error'));
        } else {
            $this->assertEquals(0, strlen($response->getContent()));
            $diseaseModel->refresh();
            $this->assertEquals(VersionStatus::Published, $diseaseModel->status);
        }
    }

    public static function archiveProvider(): array
    {
        return [
            [VersionStatus::Published, 204, false],
            [VersionStatus::Draft, 400, true],
            [VersionStatus::Archived, 400, true],
        ];
    }

    /**
     * @dataProvider archiveProvider
     */
    public function testArchive(VersionStatus $status, int $expectedStatus, bool $expectsError): void
    {
        $user = $this->createUser();
        $disease = Disease::factory()->create();
        $diseaseModel = DiseaseModel::factory()->create(['disease_id' => $disease->id, 'status' => $status]);

        $response = $this->be($user)->patchJson(route('api-disease-model-archive', ['diseaseModel' => $diseaseModel]));
        $this->assertStatus($response, $expectedStatus);

        if ($expectsError) {
            $this->assertNotNull($response->json('error'));
        } else {
            $this->assertEquals(0, strlen($response->getContent()));
            $diseaseModel->refresh();
            $this->assertEquals(VersionStatus::Archived, $diseaseModel->status);
        }
    }

    public static function cloneProvider(): array
    {
        return [
            [VersionStatus::Published, 201, false],
            [VersionStatus::Draft, 400, true],
            [VersionStatus::Archived, 201, false],
        ];
    }

    /**
     * @dataProvider cloneProvider
     */
    public function testClone(VersionStatus $status, int $expectedStatus, bool $expectsError): void
    {
        $user = $this->createUser();
        $disease = Disease::factory()->create();
        $diseaseModel = DiseaseModel::factory()->create(['disease_id' => $disease->id, 'status' => $status]);

        $response = $this->be($user)->patchJson(route('api-disease-model-clone', ['diseaseModel' => $diseaseModel]));
        $this->assertStatus($response, $expectedStatus);

        if ($expectsError) {
            $this->assertNotNull($response->json('error'));
        } else {
            $this->assertStatus($response, 201);
            $this->assertIsInt($response->json('data.id'));
            $this->assertNotEquals($diseaseModel->id, $response->json('data.id'));
            $response->assertJsonPath('data.status', 'draft');
            $this->assertNull($response->json('validationResult'));
        }
    }

    private function routeDiseaseModelShowForm(Disease $disease, string|int $version, string $entityName): string
    {
        return route(
            'api-disease-model-show-form',
            ['disease' => $disease, 'diseaseModelVersion' => $version, 'entityName' => $entityName],
        );
    }

    public function testModelShowFormWhenThereIsNoModelYet(): void
    {
        $user = $this->createUser();
        $disease = Disease::factory()->create();

        $response = $this->be($user)->getJson($this->routeDiseaseModelShowForm($disease, 'draft', Entity::Dossier->value));
        $this->assertStatus($response, 404);
        $response = $this->be($user)->getJson($this->routeDiseaseModelShowForm($disease, 'current', Entity::Dossier->value));
        $this->assertStatus($response, 404);
    }

    public static function modelShowFormProvider(): Generator
    {
        foreach (Entity::cases() as $entity) {
            yield $entity->name . ' - model: draft, ui: published' => [VersionStatus::Draft, VersionStatus::Published, $entity->value, 200, 200, 404];
            yield $entity->name . ' - model: draft, ui: draft' => [VersionStatus::Draft, VersionStatus::Draft, $entity->value, 200, 200, 404];
            yield $entity->name . ' - model: draft, ui: archived' => [VersionStatus::Draft, VersionStatus::Archived, $entity->value, 404, 404, 404];
            yield $entity->name . ' - model: draft, no-ui' => [VersionStatus::Draft, null, $entity->value, 404, 404, 404];
            yield $entity->name . ' - model: published, ui: published' => [VersionStatus::Published, VersionStatus::Published, $entity->value, 200, 404, 200];
            yield $entity->name . ' - model: published, ui: draft' => [VersionStatus::Published, VersionStatus::Draft, $entity->value, 404, 404, 404];
            yield $entity->name . ' - model: published, ui: archived' => [VersionStatus::Published, VersionStatus::Archived, $entity->value, 404, 404, 404];
            yield $entity->name . ' - model: published, no-ui' => [VersionStatus::Published, null, $entity->value, 404, 404, 404];
            yield $entity->name . ' - model: archived, ui: published' => [VersionStatus::Archived, VersionStatus::Published, $entity->value, 200, 404, 404];
            yield $entity->name . ' - model: archived, ui: draft' => [VersionStatus::Archived, VersionStatus::Draft, $entity->value, 404, 404, 404];
            yield $entity->name . ' - model: archived, ui: archived' => [VersionStatus::Archived, VersionStatus::Archived, $entity->value, 404, 404, 404];
            yield $entity->name . ' - model: archived, no-ui' => [VersionStatus::Archived, null, $entity->value, 404, 404, 404];
        }
    }

    /**
     * @dataProvider modelShowFormProvider
     */
    public function testModelShowFormShouldRespectVersionStatus(VersionStatus $modelStatus, ?VersionStatus $uiStatus, string $entityName, int $versionStatusCode, int $draftStatusCode, int $currentStatusCode): void
    {
        $user = $this->createUser();

        $disease = Disease::factory()->create();
        $model = DiseaseModel::factory()->create(['disease_id' => $disease->id, 'status' => $modelStatus]);
        if ($uiStatus !== null) {
            DiseaseModelUI::factory()->create(['disease_model_id' => $model->id, 'status' => $uiStatus]);
        }

        $response = $this->be($user)->getJson($this->routeDiseaseModelShowForm($disease, $model->version, $entityName));
        $this->assertStatus($response, $versionStatusCode);
        $response = $this->be($user)->getJson($this->routeDiseaseModelShowForm($disease, 'draft', $entityName));
        $this->assertStatus($response, $draftStatusCode);
        $response = $this->be($user)->getJson($this->routeDiseaseModelShowForm($disease, 'current', $entityName));
        $this->assertStatus($response, $currentStatusCode);
    }

    public function testModelShowForm(): void
    {
        $dataSchema = ['properties' => ['prop' => ['type' => 'string', 'format' => 'date']]];
        $uiSchema = ['prop' => $this->faker->randomAscii];

        $user = $this->createUser();
        $disease = Disease::factory()->create();
        $model = DiseaseModel::factory()->create(
            ['disease_id' => $disease->id, 'status' => VersionStatus::Published, 'dossier_schema' => json_encode($dataSchema)],
        );
        DiseaseModelUI::factory()->create(
            ['disease_model_id' => $model->id, 'status' => VersionStatus::Published, 'dossier_schema' => json_encode($uiSchema)],
        );

        $response = $this->be($user)->getJson($this->routeDiseaseModelShowForm($disease, $model->version, Entity::Dossier->value));
        $this->assertStatus($response, 200);
        $response->assertJsonPath('dataSchema.properties.data.properties.prop', $dataSchema['properties']['prop']);
        $response->assertJsonPath('uiSchema.prop', $uiSchema['prop']);
        $this->assertCount(0, $response->json('translations'));
    }
}
