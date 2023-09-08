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
class ApiDiseaseModelUIControllerTest extends FeatureTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        ConfigHelper::enableFeatureFlag('diseases_and_dossiers_enabled');
    }

    private function routeDiseaseModelUICreate(DiseaseModel $diseaseModel): string
    {
        return route('api-disease-model-ui-create', ['diseaseModel' => $diseaseModel]);
    }

    public static function validDataProvider(): array
    {
        return [
            'minimal' => [
                [
                    'dossierSchema' => '{}',
                    'contactSchema' => '{}',
                    'eventSchema' => '{}',
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
        $diseaseModel = DiseaseModel::factory()->create(['disease_id' => $disease->id]);

        $response = $this->be($user)->postJson($this->routeDiseaseModelUICreate($diseaseModel), $data);
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
        $diseaseModel = DiseaseModel::factory()->create(['disease_id' => $disease->id]);
        DiseaseModelUI::factory()->create(['disease_model_id' => $diseaseModel->id, 'status' => VersionStatus::Draft]);

        $data = [
            'dossierSchema' => '{}',
            'contactSchema' => '{}',
            'eventSchema' => '{}',
            'translations' => '{}',
        ];

        $response = $this->be($user)->postJson($this->routeDiseaseModelUICreate($diseaseModel), $data);
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
        $diseaseModel = DiseaseModel::factory()->create(['disease_id' => $disease->id]);

        $response = $this->be($user)->postJson($this->routeDiseaseModelUICreate($diseaseModel), $data);
        $this->assertStatus($response, 400);
        $response->assertJsonMissingPath('data');
        $this->assertNotNull($response->json('validationResult'));
    }

    private function routeDiseaseModelUIUpdate(DiseaseModelUI $diseaseModelUI): string
    {
        return route('api-disease-model-ui-update', ['diseaseModelUI' => $diseaseModelUI]);
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
        $diseaseModelUI = DiseaseModelUI::factory()->create(['disease_model_id' => $diseaseModel->id]);

        $response = $this->be($user)->putJson($this->routeDiseaseModelUIUpdate($diseaseModelUI), $data);
        $this->assertStatus($response, 200);
        $response->assertJsonPath('data.id', $diseaseModelUI->id);
        $response->assertJsonPath('data.status', $diseaseModelUI->status->value);
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
        $diseaseModel = DiseaseModel::factory()->create(['disease_id' => $disease->id]);
        $diseaseModelUI = DiseaseModelUI::factory()->create(['disease_model_id' => $diseaseModel->id, 'status' => $status]);

        $response = $this->be($user)->putJson($this->routeDiseaseModelUIUpdate($diseaseModelUI), $data);
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
        $diseaseModel = DiseaseModel::factory()->create(['disease_id' => $disease->id]);
        $diseaseModelUI = DiseaseModelUI::factory()->create(['disease_model_id' => $diseaseModel->id, 'status' => $status]);

        $response = $this->be($user)->delete(route('api-disease-model-ui-delete', ['diseaseModelUI' => $diseaseModelUI]));
        $this->assertStatus($response, $expectedStatus);

        if ($expectsError) {
            $this->assertNotNull($response->json('error'));
        } else {
            $this->assertEquals(0, strlen($response->getContent()));
        }

        $this->assertEquals($expectsError, !is_null(DiseaseModelUI::find($diseaseModelUI->id)));
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
        $diseaseModel = DiseaseModel::factory()->create(['disease_id' => $disease->id]);
        $diseaseModelUI = DiseaseModelUI::factory()->create(['disease_model_id' => $diseaseModel->id, 'status' => $status]);

        $response = $this->be($user)->patchJson(route('api-disease-model-ui-publish', ['diseaseModelUI' => $diseaseModelUI]));
        $this->assertStatus($response, $expectedStatus);

        if ($expectsError) {
            $this->assertNotNull($response->json('error'));
        } else {
            $this->assertEquals(0, strlen($response->getContent()));
            $diseaseModelUI->refresh();
            $this->assertEquals(VersionStatus::Published, $diseaseModelUI->status);
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
        $diseaseModel = DiseaseModel::factory()->create(['disease_id' => $disease->id]);
        $diseaseModelUI = DiseaseModelUI::factory()->create(['disease_model_id' => $diseaseModel->id, 'status' => $status]);

        $response = $this->be($user)->patchJson(route('api-disease-model-ui-archive', ['diseaseModelUI' => $diseaseModelUI]));
        $this->assertStatus($response, $expectedStatus);

        if ($expectsError) {
            $this->assertNotNull($response->json('error'));
        } else {
            $this->assertEquals(0, strlen($response->getContent()));
            $diseaseModelUI->refresh();
            $this->assertEquals(VersionStatus::Archived, $diseaseModelUI->status);
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
        $diseaseModel = DiseaseModel::factory()->create(['disease_id' => $disease->id]);
        $diseaseModelUI = DiseaseModelUI::factory()->create(['disease_model_id' => $diseaseModel->id, 'status' => $status]);

        $response = $this->be($user)->patchJson(route('api-disease-model-ui-clone', ['diseaseModelUI' => $diseaseModelUI]));
        $this->assertStatus($response, $expectedStatus);

        if ($expectsError) {
            $this->assertNotNull($response->json('error'));
        } else {
            $this->assertStatus($response, 201);
            $this->assertIsInt($response->json('data.id'));
            $this->assertNotEquals($diseaseModelUI->id, $response->json('data.id'));
            $response->assertJsonPath('data.status', 'draft');
            $this->assertNull($response->json('validationResult'));
        }
    }

    private function routeDiseaseModelUIShowForm(Disease $disease, string|int $modelVersion, string|int $uiVersion, string $entityName): string
    {
        return route(
            'api-disease-model-ui-show-form',
            ['disease' => $disease, 'diseaseModelVersion' => $modelVersion, 'diseaseModelUIVersion' => $uiVersion, 'entityName' => $entityName],
        );
    }

    public function testModelUIShowFormWhenThereIsNoUIYet(): void
    {
        $user = $this->createUser();
        $disease = Disease::factory()->create();
        DiseaseModel::factory()->create(['disease_id' => $disease->id, 'status' => VersionStatus::Published]);

        $response = $this->be($user)->getJson($this->routeDiseaseModelUIShowForm($disease, 'current', 'draft', Entity::Dossier->value));
        $this->assertStatus($response, 404);
        $response = $this->be($user)->getJson($this->routeDiseaseModelUIShowForm($disease, 'current', 'current', Entity::Dossier->value));
        $this->assertStatus($response, 404);
    }

    public static function modelUIShowFormProvider(): Generator
    {
        foreach (Entity::cases() as $entity) {
            yield $entity->name . ' - model: draft, ui: published' => [VersionStatus::Draft, VersionStatus::Published, $entity->value, 200, 404, 200];
            yield $entity->name . ' - model: draft, ui: draft' => [VersionStatus::Draft, VersionStatus::Draft, $entity->value, 200, 200, 404];
            yield $entity->name . ' - model: draft, ui: archived' => [VersionStatus::Draft, VersionStatus::Archived, $entity->value, 200, 404, 404];
            yield $entity->name . ' - model: published, ui: published' => [VersionStatus::Published, VersionStatus::Published, $entity->value, 200, 404, 200];
            yield $entity->name . ' - model: published, ui: draft' => [VersionStatus::Published, VersionStatus::Draft, $entity->value, 200, 200, 404];
            yield $entity->name . ' - model: published, ui: archived' => [VersionStatus::Published, VersionStatus::Archived, $entity->value, 200, 404, 404];
            yield $entity->name . ' - model: archived, ui: published' => [VersionStatus::Archived, VersionStatus::Published, $entity->value, 200, 404, 200];
            yield $entity->name . ' - model: archived, ui: draft' => [VersionStatus::Archived, VersionStatus::Draft, $entity->value, 200, 200, 404];
            yield $entity->name . ' - model: archived, ui: archived' => [VersionStatus::Archived, VersionStatus::Archived, $entity->value, 200, 404, 404];
        }
    }

    /**
     * @dataProvider modelUIShowFormProvider
     */
    public function testModelUIShowFormShouldRespectVersionStatus(VersionStatus $modelStatus, VersionStatus $uiStatus, string $entityName, int $versionStatusCode, int $draftStatusCode, int $currentStatusCode): void
    {
        $user = $this->createUser();

        $disease = Disease::factory()->create();
        $model = DiseaseModel::factory()->create(['disease_id' => $disease->id, 'status' => $modelStatus]);
        $ui = DiseaseModelUI::factory()->create(['disease_model_id' => $model->id, 'status' => $uiStatus]);

        $response = $this->be($user)->getJson($this->routeDiseaseModelUIShowForm($disease, $model->version, $ui->version, $entityName));
        $this->assertStatus($response, $versionStatusCode);
        $response = $this->be($user)->getJson($this->routeDiseaseModelUIShowForm($disease, $model->version, 'draft', $entityName));
        $this->assertStatus($response, $draftStatusCode);
        $response = $this->be($user)->getJson($this->routeDiseaseModelUIShowForm($disease, $model->version, 'current', $entityName));
        $this->assertStatus($response, $currentStatusCode);
    }

    public function testModeUIlShowForm(): void
    {
        $dataSchema = ['properties' => ['prop' => ['type' => 'string', 'format' => 'date']]];
        $uiSchema = ['prop' => $this->faker->randomAscii];

        $user = $this->createUser();
        $disease = Disease::factory()->create();
        $model = DiseaseModel::factory()->create(
            ['disease_id' => $disease->id, 'status' => VersionStatus::Published, 'dossier_schema' => json_encode($dataSchema)],
        );
        $ui = DiseaseModelUI::factory()->create(
            ['disease_model_id' => $model->id, 'status' => VersionStatus::Published, 'dossier_schema' => json_encode($uiSchema)],
        );

        $response = $this->be($user)->getJson(
            $this->routeDiseaseModelUIShowForm($disease, $model->version, $ui->version, Entity::Dossier->value),
        );
        $this->assertStatus($response, 200);
        $response->assertJsonPath('dataSchema.properties.data.properties.prop', $dataSchema['properties']['prop']);
        $response->assertJsonPath('uiSchema.prop', $uiSchema['prop']);
        $this->assertCount(0, $response->json('translations'));
    }
}
