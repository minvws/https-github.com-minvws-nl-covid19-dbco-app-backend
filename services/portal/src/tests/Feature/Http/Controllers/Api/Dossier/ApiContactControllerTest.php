<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api\Dossier;

use App\Models\Disease\Disease;
use App\Models\Disease\DiseaseModel;
use App\Models\Disease\VersionStatus;
use App\Models\Dossier\Dossier;
use App\Services\Disease\HasMany\HasManyType;
use Illuminate\Testing\TestResponse;
use Tests\Feature\FeatureTestCase;
use Tests\Helpers\ConfigHelper;

use function base_path;
use function file_get_contents;
use function route;

/**
 * @group dossier
 */
class ApiContactControllerTest extends FeatureTestCase
{
    private Dossier $dossier;

    protected function setUp(): void
    {
        parent::setUp();

        ConfigHelper::enableFeatureFlag('diseases_and_dossiers_enabled');

        /** @var Disease $disease */
        $disease = Disease::factory()->create();

        /** @var DiseaseModel $diseaseModel */
        $diseaseModel = DiseaseModel::factory()->create([
            'disease_id' => $disease->id,
            'status' => VersionStatus::Published,
            'dossier_schema' => file_get_contents(base_path('tests/fixtures/disease/simple/dossier.schema.json')),
            'contact_schema' => file_get_contents(base_path('tests/fixtures/disease/simple/contact.schema.json')),
            'event_schema' => file_get_contents(base_path('tests/fixtures/disease/simple/event.schema.json')),
        ]);

        $this->be($this->createUser());

        $this->dossier = Dossier::factory()->create([
            'disease_model_id' => $diseaseModel->id,
        ]);
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
        $response = $this->patchJson('/api/dossiers/' . $this->dossier->id . '/contacts/validate', $data);
        $this->assertStatus($response, $expectedStatusCode);
    }

    /**
     * @dataProvider validateCreateProvider
     */
    public function testCreateContactValidation(array $data, int $expectedStatusCode): void
    {
        $response = $this->postJson('/api/dossiers/' . $this->dossier->id . '/contacts', $data);
        $this->assertStatus($response, $expectedStatusCode === 200 ? 201 : $expectedStatusCode);
    }

    public function testCreateContact(): void
    {
        $data = $this->generateContactData();
        $response = $this->postContact($data);
        $this->assertStatus($response, 201);
        $response->assertJsonPath('data.person.firstName', $data['person']['firstName']);
        $response->assertJsonPath('data.person.city', $data['person']['city']);
        $id = $response->json('data.id');
        $this->assertIsInt($id);
        $response = $this->getJson(route('api-contact-show', ['dossierContact' => $id]));
        $response->assertJsonPath('data.person.firstName', $data['person']['firstName']);
        $response->assertJsonPath('data.person.city', $data['person']['city']);
    }

    public function testCreateContactForListView(): void
    {
        $data = $this->generateContactData();
        $response = $this->postContact($data, ['view' => HasManyType::VIEW_LIST]);
        $this->assertStatus($response, 201);
        $response->assertJsonPath('data.person.firstName', $data['person']['firstName']);
        $response->assertJsonMissingPath('data.person.city');
        $id = $response->json('data.id');
        $this->assertIsInt($id);
        // even though we posted city in the data above, make sure it hasn't been stored
        $response = $this->getJson(route('api-contact-show', ['dossierContact' => $id]));
        $response->assertJsonPath('data.person.firstName', $data['person']['firstName']);
        $response->assertJsonPath('data.person.city', null);
    }

    public function testShowContact(): void
    {
        $data = $this->generateContactData();
        $contactId = $this->postContact($data)->assertStatus(201)->json('data.id');
        $response = $this->getJson('/api/contacts/' . $contactId);
        $this->assertStatus($response, 200);
        $response->assertJsonPath('data.id', $contactId);
        $response->assertJsonPath('data.person.firstName', $data['person']['firstName']);
        $response->assertJsonPath('data.person.lastName', $data['person']['lastName']);
        $response->assertJsonPath('data.person.city', $data['person']['city']);
        $this->assertIsString($response->json('links.form.href'));
    }

    public function testShowContactViewList(): void
    {
        $data = $this->generateContactData();
        $contactId = $this->postContact($data)->assertStatus(201)->json('data.id');
        $response = $this->getJson(route('api-contact-show', ['dossierContact' => $contactId, 'view' => HasManyType::VIEW_LIST]));
        $this->assertStatus($response, 200);
        $response->assertJsonPath('data.id', $contactId);
        $response->assertJsonPath('data.person.firstName', $data['person']['firstName']);
        $response->assertJsonPath('data.person.lastName', $data['person']['lastName']);
        $response->assertJsonMissingPath('data.person.city');
        $this->assertIsString($response->json('links.editModal.href'));
    }

    public function testShowContactInDossier(): void
    {
        $data = $this->generateContactData();
        $this->postContact($data)->assertStatus(201);
        $response = $this->getJson('/api/dossiers/' . $this->dossier->id);
        $this->assertStatus($response, 200);
        $response->assertJsonPath('data.contacts.data.0.data.person.firstName', $data['person']['firstName']);
        $response->assertJsonPath('data.contacts.data.0.data.person.lastName', $data['person']['lastName']);
        $response->assertJsonMissingPath('data.contacts.data.0.data.person.city');
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
        $data = $this->generateContactData();
        $contactId = $this->postContact($data)->assertStatus(201)->json('data.id');
        $response = $this->patchJson('/api/contacts/' . $contactId . '/validate', $update);
        $this->assertStatus($response, $expectedStatusCode);
    }

    /**
     * @dataProvider validateUpdateProvider
     */
    public function testUpdateContactValidation(array $update, int $expectedStatusCode): void
    {
        $data = $this->generateContactData();
        $contactId = $this->postContact($data)->assertStatus(201)->json('data.id');
        $response = $this->putJson('/api/contacts/' . $contactId, $update);
        $this->assertStatus($response, $expectedStatusCode);
    }

    public function testUpdateContact(): void
    {
        $originalData = $this->generateContactData();
        $contactId = $this->postContact($originalData)->assertStatus(201)->json('data.id');

        $update = [
            'person' => [
                'lastName' => $this->faker->lastName(),
            ],
        ];

        $response = $this->putJson('/api/contacts/' . $contactId, $update);
        $this->assertStatus($response, 200);
        $updatedData = $response->json('data');
        $this->assertEquals($contactId, $updatedData['id']);
        $this->assertEquals($originalData['person']['firstName'], $updatedData['person']['firstName']);
        $this->assertEquals($update['person']['lastName'], $updatedData['person']['lastName']);
    }

    private function generateContactData(): array
    {
        return [
            'person' => [
                'firstName' => $this->faker->firstName(),
                'lastName' => $this->faker->lastName(),
                'city' => $this->faker->city,
            ],
        ];
    }

    private function postContact(array $data, array $query = []): TestResponse
    {
        return $this->postJson(route('api-contact-create', ['dossier' => $this->dossier, ...$query]), $data);
    }
}
