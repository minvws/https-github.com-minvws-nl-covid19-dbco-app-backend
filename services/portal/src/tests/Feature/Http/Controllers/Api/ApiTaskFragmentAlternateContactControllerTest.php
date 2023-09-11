<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use DateTimeImmutable;
use MinVWS\DBCO\Enum\Models\Gender;
use MinVWS\DBCO\Enum\Models\Relationship;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use Tests\Feature\FeatureTestCase;

use function sprintf;

final class ApiTaskFragmentAlternateContactControllerTest extends FeatureTestCase
{
    public function testAlternateContactFragmentIsPresentInResponseByDefault(): void
    {
        $user = $this->createUser();
        $task = $this->createTaskForUser($user, ['created_at' => new DateTimeImmutable('now')]);

        $response = $this->be($user)->get(sprintf('/api/tasks/%s/fragments', $task->uuid));
        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertArrayHasKey('alternateContact', $data);
        $this->assertIsArray($data['alternateContact']);
    }

    public function testStoreAlternateContactFragment(): void
    {
        $user = $this->createUser();
        $task = $this->createTaskForUser($user, ['created_at' => new DateTimeImmutable('now')]);

        $input = [
            'hasAlternateContact' => YesNoUnknown::yes()->value,
            'firstname' => 'Alternate',
            'lastname' => 'Contact',
            'gender' => Gender::male()->value,
            'explanation' => 'Unreasonable',
            'relationship' => Relationship::partner()->value,
        ];

        $testFragmentData = function (array $output) use ($input): void {
            $this->assertEquals($input['hasAlternateContact'], $output['hasAlternateContact']);
            $this->assertEquals($input['gender'], $output['gender']);
            $this->assertEquals($input['relationship'], $output['relationship']);
            $this->assertEquals($input['firstname'], $output['firstname']);
            $this->assertEquals($input['lastname'], $output['lastname']);
            $this->assertEquals($input['explanation'], $output['explanation']);
        };

        $response = $this->be($user)
            ->putJson(
                sprintf('/api/tasks/%s/fragments', $task->uuid),
                ['alternateContact' => $input],
            );
        $response->assertStatus(200);
        $testFragmentData($response->json()['data']['alternateContact']);

        // check storage
        $response = $this->be($user)->get(sprintf('/api/tasks/%s/fragments', $task->uuid));
        $response->assertStatus(200);
        $testFragmentData($response->json()['data']['alternateContact']);
    }
}
