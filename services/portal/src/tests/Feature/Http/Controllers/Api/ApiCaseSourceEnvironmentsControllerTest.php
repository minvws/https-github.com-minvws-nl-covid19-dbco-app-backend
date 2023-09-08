<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use MinVWS\DBCO\Enum\Models\ContextCategory;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

#[Group('case-fragment')]
#[Group('case-fragment-source-environments')]
class ApiCaseSourceEnvironmentsControllerTest extends FeatureTestCase
{
    public function testGet(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user, ['schema_version' => 4]);

        $response = $this->be($user)->get('/api/cases/' . $case->uuid . '/fragments/source-environments');
        $response->assertStatus(200);

        $response = $this->be($user)->get('/api/cases/nonexisting/fragments/source-environments');
        $response->assertStatus(404);
    }

    /**
     * Test index fragment storage.
     */
    public function testPut(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user, ['schema_version' => 4]);

        // check no required fields
        $response = $this->be($user)->putJson('/api/cases/' . $case->uuid . '/fragments/source-environments');
        $response->assertStatus(200);

        $likelySourceEnvironments = [
            ContextCategory::feest(),
            ContextCategory::buitenland(),
        ];

        // check storage
        $response = $this->be($user)->putJson('/api/cases/' . $case->uuid . '/fragments/source-environments', [
            'hasLikelySourceEnvironments' => YesNoUnknown::yes(),
            'likelySourceEnvironments' => $likelySourceEnvironments,
        ]);
        $response->assertStatus(200);

        $data = $response->json();
        $this->assertEquals(YesNoUnknown::yes(), $data['data']['hasLikelySourceEnvironments']);
        $this->assertEquals($likelySourceEnvironments, $data['data']['likelySourceEnvironments']);

        // check if really stored
        $response = $this->be($user)->get('/api/cases/' . $case->uuid . '/fragments/source-environments');
        $data = $response->json();
        $this->assertEquals(YesNoUnknown::yes(), $data['data']['hasLikelySourceEnvironments']);
        $this->assertEquals($likelySourceEnvironments, $data['data']['likelySourceEnvironments']);
    }
}
