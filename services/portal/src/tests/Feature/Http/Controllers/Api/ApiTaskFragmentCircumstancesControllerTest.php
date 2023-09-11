<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use DateTimeImmutable;
use MinVWS\DBCO\Enum\Models\PersonalProtectiveEquipment;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use Tests\Feature\FeatureTestCase;

use function sprintf;

final class ApiTaskFragmentCircumstancesControllerTest extends FeatureTestCase
{
    public function testGetEmpty(): void
    {
        $user = $this->createUser();
        $task = $this->createTaskForUser($user, ['created_at' => new DateTimeImmutable('now')]);

        $response = $this->be($user)->get(sprintf('/api/tasks/%s/fragments', $task->uuid));
        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertArrayHasKey('circumstances', $data);
        $this->assertIsArray($data['circumstances']);
    }

    public function testStore(): void
    {
        $user = $this->createUser();
        $task = $this->createTaskForUser($user, ['created_at' => new DateTimeImmutable('now')]);

        $input = [
            'wasUsingPPE' => YesNoUnknown::yes()->value,
            'usedPersonalProtectiveEquipment' => [
                PersonalProtectiveEquipment::gloves(),
                PersonalProtectiveEquipment::mask(),
            ],
            'ppeType' => 'PCR',
            'ppeReplaceFrequency' => '2',
            'ppeMedicallyCompetent' => true,
        ];

        $testFragmentData = function (array $output) use ($input): void {
            $this->assertEquals($input['wasUsingPPE'], $output['wasUsingPPE']);
            $this->assertEquals($input['usedPersonalProtectiveEquipment'], $output['usedPersonalProtectiveEquipment']);
            $this->assertEquals($input['ppeType'], $output['ppeType']);
            $this->assertEquals($input['ppeReplaceFrequency'], $output['ppeReplaceFrequency']);
            $this->assertEquals($input['ppeMedicallyCompetent'], $output['ppeMedicallyCompetent']);
        };

        $uri = sprintf('/api/tasks/%s/fragments', $task->uuid);
        $response = $this->be($user)->putJson($uri, ['circumstances' => $input]);
        $response->assertStatus(200);
        $testFragmentData($response->json()['data']['circumstances']);

        // check storage
        $response = $this->be($user)->get($uri);
        $response->assertStatus(200);
        $testFragmentData($response->json()['data']['circumstances']);
    }
}
