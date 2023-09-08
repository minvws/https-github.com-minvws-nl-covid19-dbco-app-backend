<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use Carbon\CarbonImmutable;
use Tests\Feature\FeatureTestCase;

use function sprintf;

final class ApiTaskFragmentAlternativeLanguageControllerTest extends FeatureTestCase
{
    public function testGetAlternativeLanguageFragment(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $task = $this->createTaskForCase($case, [
            'created_at' => CarbonImmutable::now(),
        ]);

        $response = $this->be($user)->getJson(sprintf('/api/tasks/%s/fragments/alternativeLanguage', $task->uuid));
        $response->assertStatus(200);

        $data = $response->json();
        $this->assertFalse(isset($data['validationResult']));
    }

    public function testPutAlternativeLanguageFragmentWithEmptyPayload(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $task = $this->createTaskForCase($case, [
            'created_at' => CarbonImmutable::now(),
        ]);

        $response = $this->be($user)->putJson(sprintf('/api/tasks/%s/fragments/alternativeLanguage', $task->uuid), []);
        $response->assertStatus(200);

        $data = $response->json();
        $this->assertFalse(isset($data['validationResult']));
    }
}
