<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use Tests\Feature\FeatureTestCase;

use function array_merge;
use function sprintf;

final class ApiCasePrincipalContextualSettingsControllerTest extends FeatureTestCase
{
    public function testEmptyPayload(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);

        $response = $this->be($user)->putJson(sprintf(
            '/api/cases/%s/fragments/principalContextualSettings',
            $case->uuid,
        ));
        $response->assertStatus(200);
    }

    public function testFullPayload(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);

        $inputData = [
            'hasPrincipalContextualSettings' => true,
            'items' => ['item1', 'item2'],
            'otherItems' => ['otherItem1', 'otherItem2'],
        ];
        $response = $this->be($user)->putJson(
            sprintf('/api/cases/%s/fragments/principalContextualSettings', $case->uuid),
            $inputData,
        );
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals(array_merge(['schemaVersion' => $data['schemaVersion']], $inputData), $data);
    }
}
