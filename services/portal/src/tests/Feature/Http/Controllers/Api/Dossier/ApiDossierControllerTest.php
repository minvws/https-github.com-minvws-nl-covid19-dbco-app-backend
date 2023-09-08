<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api\Dossier;

use App\Models\Disease\Disease;
use App\Models\Disease\DiseaseModel;
use App\Models\Disease\VersionStatus;
use Illuminate\Testing\TestResponse;
use Tests\Feature\FeatureTestCase;
use Tests\Helpers\ConfigHelper;

use function base_path;
use function file_get_contents;
use function route;

/**
 * @group dossier
 */
class ApiDossierControllerTest extends FeatureTestCase
{
    private DiseaseModel $diseaseModel;

    protected function setUp(): void
    {
        parent::setUp();

        ConfigHelper::enableFeatureFlag('diseases_and_dossiers_enabled');

        /** @var Disease $disease */
        $disease = Disease::factory()->create();
        $this->diseaseModel = DiseaseModel::factory()->create([
            'disease_id' => $disease->id,
            'status' => VersionStatus::Published,
            'dossier_schema' => file_get_contents(base_path('tests/fixtures/disease/simple/dossier.schema.json')),
            'contact_schema' => file_get_contents(base_path('tests/fixtures/disease/simple/contact.schema.json')),
            'event_schema' => file_get_contents(base_path('tests/fixtures/disease/simple/event.schema.json')),
        ]);

        $this->be($this->createUser());
    }

    public static function validateCreateProvider(): array
    {
        return [
            'required-field-missing' => [['person' => ['lastName' => 'Jansen']], 400],
            'required-field-null' => [['person' => ['firstName' => null, 'lastName' => 'Jansen']], 400],
            'invalid-field->type' => [['person' => ['firstName' => 1234, 'lastName' => 'Jansen']], 400],
            'valid' => [['person' => ['firstName' => 'Jan', 'lastName' => 'Jansen']], 200],
        ];
    }

    /**
     * @dataProvider validateCreateProvider
     */
    public function testValidateForCreate(array $data, int $expectedStatusCode): void
    {
        $response = $this->patchJson(
            '/api/diseases/' . $this->diseaseModel->disease->id . '/models/' . $this->diseaseModel->version . '/dossiers/validate',
            $data,
        );
        $this->assertStatus($response, $expectedStatusCode);
    }

    /**
     * @dataProvider validateCreateProvider
     */
    public function testCreateDossierValidation(array $data, int $expectedStatusCode): void
    {
        $response = $this->postJson(
            '/api/diseases/' . $this->diseaseModel->disease->id . '/models/' . $this->diseaseModel->version . '/dossiers',
            $data,
        );
        $this->assertStatus($response, $expectedStatusCode === 200 ? 201 : $expectedStatusCode);
    }

    public function testCreateDossier(): void
    {
        $data = $this->generateDossierData();
        $response = $this->postDossier($data);
        $response->assertJsonPath('data.person.firstName', $data['person']['firstName']);
        $id = $response->json('data.id');
        $this->assertIsInt($id);
    }

    public function testShowDossier(): void
    {
        $data = $this->generateDossierData();
        $dossierId = $this->postDossier($data)->assertStatus(201)->json('data.id');
        $response = $this->getJson('/api/dossiers/' . $dossierId);
        $this->assertStatus($response, 200);
        $response->assertJsonPath('data.id', $dossierId);
        $response->assertJsonPath('data.diseaseModel.id', $this->diseaseModel->id);
        $response->assertJsonMissingPath('data.diseaseModel.dossierSchema');
        $response->assertJsonPath('data.diseaseModel.disease.id', $this->diseaseModel->disease->id);
        $response->assertJsonPath('data.diseaseModel.disease.name', $this->diseaseModel->disease->name);
        $this->assertIsString($response->json('links.form.href'));
    }

    public static function validateUpdateProvider(): array
    {
        return [
            'required-field-null' => [['person' => ['firstName' => null]], 400],
            'invalid-field->type' => [['person' => ['firstName' => 1234]], 400],
            'partial-update' => [['person' => ['lastName' => 'Doe']], 200],
            'full-update' => [['person' => ['firstName' => 'Jan', 'lastName' => 'Doe']], 200],
        ];
    }

    /**
     * @dataProvider validateUpdateProvider
     */
    public function testValidateForUpdate(array $update, int $expectedStatusCode): void
    {
        $data = $this->generateDossierData();
        $dossierId = $this->postDossier($data)->assertStatus(201)->json('data.id');
        $response = $this->patchJson('/api/dossiers/' . $dossierId . '/validate', $update);
        $this->assertStatus($response, $expectedStatusCode);
    }

    /**
     * @dataProvider validateUpdateProvider
     */
    public function testUpdateDossierValidation(array $update, int $expectedStatusCode): void
    {
        $data = $this->generateDossierData();
        $dossierId = $this->postDossier($data)->assertStatus(201)->json('data.id');
        $response = $this->putJson('/api/dossiers/' . $dossierId, $update);
        $this->assertStatus($response, $expectedStatusCode);
    }

    public function testUpdateDossier(): void
    {
        $originalData = $this->generateDossierData();
        $dossierId = $this->postDossier($originalData)->assertStatus(201)->json('data.id');

        $update = [
            'person' => [
                'lastName' => $this->faker->lastName(),
            ],
        ];

        $response = $this->putJson('/api/dossiers/' . $dossierId, $update);
        $this->assertStatus($response, 200);
        $updatedData = $response->json('data');
        $this->assertEquals($dossierId, $updatedData['id']);
        $this->assertEquals($originalData['person']['firstName'], $updatedData['person']['firstName']);
        $this->assertEquals($update['person']['lastName'], $updatedData['person']['lastName']);
    }

    private function generateDossierData(): array
    {
        return [
            'person' => [
                'firstName' => $this->faker->firstName(),
                'lastName' => $this->faker->lastName(),
            ],
        ];
    }

    private function postDossier(array $data): TestResponse
    {
        return $this->postJson(
            route('api-dossier-create', ['disease' => $this->diseaseModel->disease, 'diseaseModelVersion' => $this->diseaseModel->version]),
            $data,
        );
    }
}
