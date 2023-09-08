<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use Carbon\CarbonImmutable;
use Tests\Feature\FeatureTestCase;

final class TaskControllerTest extends FeatureTestCase
{
    public function testGetQuestionnaireNewTaskShouldRender(): void
    {
        $user = $this->createUser([
            'consented_at' => CarbonImmutable::now(),
        ]);
        $case = $this->createCaseForUser($user, [
            'dateOfSymptomOnset' => CarbonImmutable::now()->subDays(3),
        ]);

        // check minimum fields required for storage
        $response = $this->be($user)->postJson('/api/cases/' . $case->uuid . '/tasks', [
            'task' => [
                'dateOfLastExposure' => null,
                'group' => 'positivesource',
                'label' => 'James',
                'category' => '3b',
            ],
        ]);
        $response->assertStatus(200);
        $task = $response->json()['task'];

        $this->assertNotEmpty($task['uuid']);
    }
}
