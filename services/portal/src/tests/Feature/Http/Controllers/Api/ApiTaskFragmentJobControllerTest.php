<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use Carbon\CarbonImmutable;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function array_merge;

#[Group('task-fragment-job')]
final class ApiTaskFragmentJobControllerTest extends FeatureTestCase
{
    public function testPutJobFragmentWithEmptyPayload(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $task = $this->createTaskForCase($case, [
            'created_at' => CarbonImmutable::now(),
        ]);

        $response = $this->be($user)->putJson('/api/tasks/' . $task->uuid . '/fragments/job', []);
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertFalse(isset($data['validationResult']));
    }

    public function testPutJobFragmentWithFullPayload(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $task = $this->createTaskForCase($case, [
            'created_at' => CarbonImmutable::now(),
        ]);

        $payload = [
            'worksInAviation' => YesNoUnknown::yes()->value,
            'worksInHealthCare' => YesNoUnknown::no()->value,
            'healthCareFunction' => 'Nurse',
        ];
        $response = $this->be($user)->putJson('/api/tasks/' . $task->uuid . '/fragments/job', $payload);
        $response->assertStatus(200);
        $data = $response->json();

        $this->assertEquals(array_merge(
            ['schemaVersion' => 1],
            $payload,
        ), $data['data']);
    }

    public function testGetJobFragment(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $task = $this->createTaskForCase($case, [
            'created_at' => CarbonImmutable::now(),
        ]);

        $payload = [
            'worksInAviation' => YesNoUnknown::yes()->value,
            'worksInHealthCare' => YesNoUnknown::no()->value,
            'healthCareFunction' => 'Nurse',
        ];
        $response = $this->be($user)->putJson('/api/tasks/' . $task->uuid . '/fragments/job', $payload);
        $response->assertStatus(200);
        $dataPut = $response->json('data');

        $response = $this->be($user)->getJson('/api/tasks/' . $task->uuid . '/fragments/job');
        $response->assertStatus(200);
        $dataGet = $response->json('data');

        $this->assertEquals($dataPut, $dataGet);
    }
}
