<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use DateTimeImmutable;
use MinVWS\DBCO\Enum\Models\Symptom;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use Tests\Feature\FeatureTestCase;

use function sprintf;

class ApiTaskFragmentSymptomsControllerTest extends FeatureTestCase
{
    public function testGetEmpty(): void
    {
        $user = $this->createUser();
        $task = $this->createTaskForUser($user, ['created_at' => new DateTimeImmutable('now')]);

        $response = $this->be($user)->get(sprintf('/api/tasks/%s/fragments', $task->uuid));
        $response->assertStatus(200);
        $this->assertNotEmpty($response->json('data')['symptoms']);
    }

    public function testStore(): void
    {
        $user = $this->createUser();
        $task = $this->createTaskForUser($user, ['created_at' => new DateTimeImmutable('now')]);

        $input = [
            'hasSymptoms' => YesNoUnknown::yes()->value,
            'symptoms' => [
                Symptom::shortnessOfBreath(),
                Symptom::malaise(),
            ],
            'otherSymptoms' => ['High heart rate', 'Pain in the back'],
            'dateOfSymptomOnset' => '2021-09-30',
        ];

        $testFragmentData = function (array $output) use ($input): void {
            $this->assertEquals($input['hasSymptoms'], $output['hasSymptoms']);
            $this->assertEquals($input['symptoms'], $output['symptoms']);
            $this->assertEquals($input['otherSymptoms'], $output['otherSymptoms']);
            $this->assertEquals($input['dateOfSymptomOnset'], $output['dateOfSymptomOnset']);
        };

        $uri = sprintf('/api/tasks/%s/fragments', $task->uuid);

        $response = $this->be($user)->putJson($uri, ['symptoms' => $input]);
        $response->assertStatus(200);
        $testFragmentData($response->json()['data']['symptoms']);

        // check storage
        $response = $this->be($user)->get($uri);
        $response->assertStatus(200);
        $testFragmentData($response->json()['data']['symptoms']);
    }
}
