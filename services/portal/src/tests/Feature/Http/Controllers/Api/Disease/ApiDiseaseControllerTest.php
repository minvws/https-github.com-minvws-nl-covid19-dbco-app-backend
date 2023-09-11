<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api\Disease;

use App\Models\Disease\Disease;
use App\Models\Disease\DiseaseModel;
use App\Models\Disease\VersionStatus;
use Tests\Feature\FeatureTestCase;
use Tests\Helpers\ConfigHelper;

use function route;
use function strlen;

/**
 * @group disease
 */
class ApiDiseaseControllerTest extends FeatureTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        ConfigHelper::enableFeatureFlag('diseases_and_dossiers_enabled');
    }

    public function testAllDiseases(): void
    {
        $user = $this->createUser();

        $mpox = Disease::factory()->create(['code' => 'mpox', 'name' => 'Mpox']);
        $birdflu = Disease::factory()->create(['code' => 'birdflu', 'name' => 'Vogelgriep']);
        $covid = Disease::factory()->create(['code' => 'covid19', 'name' => 'Covid-19']);
        DiseaseModel::factory()->create(['disease_id' => $covid->id, 'status' => VersionStatus::Published, 'version' => 2]);

        $response = $this->be($user)->getJson(route('api-disease-index'));
        $this->assertStatus($response, 200);
        $response->assertJsonCount(3);
        $response->assertJson([
            ['id' => $covid->id, 'code' => 'covid19', 'name' => $covid->name, 'currentVersion' => 2, 'isActive' => true],
            ['id' => $mpox->id, 'code' => 'mpox', 'name' => $mpox->name, 'currentVersion' => null, 'isActive' => false],
            ['id' => $birdflu->id, 'code' => 'birdflu', 'name' => $birdflu->name, 'currentVersion' => null, 'isActive' => false],
        ]);
    }

    public function testActiveDiseasesStatus(): void
    {
        $user = $this->createUser();

        $disease = Disease::factory()->create();

        $response = $this->be($user)->getJson(route('api-disease-active'));
        $this->assertStatus($response, 200);
        $response->assertJsonCount(0);

        DiseaseModel::factory()->create(['disease_id' => $disease->id, 'version' => 1, 'status' => VersionStatus::Archived]);
        $v2 = DiseaseModel::factory()->create(['disease_id' => $disease->id, 'version' => 2, 'status' => VersionStatus::Draft]);

        $response = $this->be($user)->getJson(route('api-disease-active'));
        $this->assertStatus($response, 200);
        $response->assertJsonCount(0);

        $v2->status = VersionStatus::Published;
        $v2->save();

        $response = $this->be($user)->getJson(route('api-disease-active'));
        $this->assertStatus($response, 200);
        $response->assertJsonCount(1);

        DiseaseModel::factory()->create(['disease_id' => $disease->id, 'version' => 3, 'status' => VersionStatus::Draft]);

        $response = $this->be($user)->getJson(route('api-disease-active'));
        $this->assertStatus($response, 200);
        $response->assertJsonCount(1);
    }

    public function testActiveDiseasesOrdering(): void
    {
        $user = $this->createUser();

        $mpox = Disease::factory()->create(['code' => 'mpox', 'name' => 'Mpox']);
        DiseaseModel::factory()->create(['disease_id' => $mpox->id, 'status' => VersionStatus::Published]);
        $birdflu = Disease::factory()->create(['code' => 'birdflu', 'name' => 'Vogelgriep']);
        DiseaseModel::factory()->create(['disease_id' => $birdflu->id, 'status' => VersionStatus::Published]);
        $covid = Disease::factory()->create(['code' => 'covid19', 'name' => 'Covid-19']);
        DiseaseModel::factory()->create(['disease_id' => $covid->id, 'status' => VersionStatus::Published]);

        $response = $this->be($user)->getJson(route('api-disease-active'));
        $this->assertStatus($response, 200);
        $response->assertJsonCount(3);
        $response->assertJson([
            ['id' => $covid->id, 'code' => 'covid19', 'name' => $covid->name],
            ['id' => $mpox->id, 'code' => 'mpox', 'name' => $mpox->name],
            ['id' => $birdflu->id, 'code' => 'birdflu', 'name' => $birdflu->name],
        ]);
    }

    public function testShow(): void
    {
        $user = $this->createUser();
        $disease = Disease::factory()->create();
        $response = $this->be($user)->getJson(route('api-disease-show', ['disease' => $disease]));
        $this->assertStatus($response, 200);
        $response->assertJsonPath('data.id', $disease->id);
        $response->assertJsonPath('data.code', $disease->code);
        $response->assertJsonPath('data.name', $disease->name);
        $this->assertArrayHasKey('validationResult', $response->json());
        $this->assertNull($response->json('validationResult'));
    }

    public function testCreate(): void
    {
        $user = $this->createUser();
        $data = ['code' => 'covid19', 'name' => 'Covid-19'];
        $response = $this->be($user)->postJson(route('api-disease-create'), $data);
        $this->assertStatus($response, 201);
        $this->assertIsInt($response->json('data.id'));
        $response->assertJsonPath('data.code', $data['code']);
        $response->assertJsonPath('data.name', $data['name']);
    }

    public static function invalidDataProvider(): array
    {
        return [
            [['code' => null, 'name' => null]],
            [['code' => 'code', 'name' => null]],
            [['code' => null, 'name' => 'name']],
        ];
    }

    public static function invalidCreateDataProvider(): array
    {
        return [
            [[]],
            [['code' => 'code']],
            [['name' => 'name']],
        ];
    }

    /**
     * @dataProvider invalidCreateDataProvider
     * @dataProvider invalidDataProvider
     */
    public function testInvalidCreate(array $data): void
    {
        $user = $this->createUser();
        $response = $this->be($user)->postJson(route('api-disease-create'), $data);
        $this->assertStatus($response, 400);
        $response->assertJsonMissingPath('data');
        $this->assertNotNull($response->json('validationResult'));
    }

    public static function validUpdateDataProvider(): array
    {
        return [
            [[]],
            [['code' => 'code']],
            [['name' => 'name']],
        ];
    }

    /**
     * @dataProvider validUpdateDataProvider
     */
    public function testUpdate(array $data): void
    {
        $user = $this->createUser();
        $disease = Disease::factory()->create();
        $response = $this->be($user)->putJson(route('api-disease-update', ['disease' => $disease]), $data);
        $this->assertStatus($response, 200);
        $response->assertJsonPath('data.id', $disease->id);
        foreach ($data as $key => $value) {
            $response->assertJsonPath('data.' . $key, $value);
        }
        $this->assertNull($response->json('validationResult'));
    }

    public static function invalidUpdateDataProvider(): array
    {
        return [
            [['code' => null]],
            [['code' => 1234]],
            [['name' => null]],
            [['name' => 1234]],
        ];
    }

    /**
     * @dataProvider invalidUpdateDataProvider
     * @dataProvider invalidDataProvider
     */
    public function testInvalidUpdate(array $data): void
    {
        $user = $this->createUser();
        $disease = Disease::factory()->create();
        $response = $this->be($user)->putJson(route('api-disease-update', ['disease' => $disease]), $data);
        $this->assertStatus($response, 400);
        $response->assertJsonMissingPath('data');
        $this->assertNotNull($response->json('validationResult'));
    }

    public function testDelete(): void
    {
        $user = $this->createUser();
        $disease = Disease::factory()->create();
        $response = $this->be($user)->delete(route('api-disease-delete', ['disease' => $disease]));
        $this->assertStatus($response, 204);
        $this->assertEquals(0, strlen($response->getContent()));
        $this->assertNull(Disease::find($disease->id));
    }

    public function testDeleteShouldFailIfAModelExists(): void
    {
        $user = $this->createUser();
        $disease = Disease::factory()->create();
        DiseaseModel::factory()->create(['disease_id' => $disease->id]);
        $response = $this->be($user)->delete(route('api-disease-delete', ['disease' => $disease]));
        $this->assertStatus($response, 400);
        $this->assertNotNull($response->json('error'));
    }
}
