<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Task\General;
use Carbon\CarbonImmutable;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function array_intersect;
use function array_keys;
use function config;
use function sprintf;

#[Group('task-fragment')]
class ApiTaskFragmentControllerTest extends FeatureTestCase
{
    public function testGetSingleFragmentRetreival(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $task = $this->createTaskForCase($case, [
            'created_at' => CarbonImmutable::now(),
        ]);

        $response = $this->be($user)->get('/api/tasks/' . $task->uuid . '/fragments/general');
        $response->assertStatus(200);
        $data = $response->json();

        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('firstname', $data['data']);
        $this->assertEquals($task->label, $data['data']['firstname']); // based on label
    }

    public function testGetSingleNotFound(): void
    {
        $this->be($this->createUser());

        $response = $this->get('/api/tasks/non-existing-uuid/fragments/general');
        $response->assertStatus(404);
    }

    public function testGetPartialMultiFragmentRetrieval(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $task = $this->createTaskForCase($case, [
            'created_at' => CarbonImmutable::now(),
        ]);

        $response = $this->be($user)->get(sprintf('/api/tasks/%s/fragments/?names=general,circumstances', $task->uuid));
        $response->assertStatus(200);
        $data = $response->json();

        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('general', $data['data']);
        $this->assertArrayHasKey('firstname', $data['data']['general']);
        $this->assertEquals($task->label, $data['data']['general']['firstname']);
        $this->assertArrayHasKey('circumstances', $data['data']);
        $this->assertArrayHasKey('wasUsingPPE', $data['data']['circumstances']);
        $this->assertEquals(null, $data['data']['circumstances']['wasUsingPPE']);
    }

    public function testGetMultiFragmentRetrieval(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $task = $this->createTaskForCase($case, [
            'created_at' => CarbonImmutable::now(),
        ]);

        $response = $this->be($user)->get('/api/tasks/' . $task->uuid . '/fragments/');
        $response->assertStatus(200);
        $data = $response->json();

        $this->assertArrayHasKey('data', $data);

        $responseDataValues = array_intersect(
            [
                'general',
                'inform',
                'alternativeLanguage',
                'circumstances',
                'symptoms',
                'test',
                'vaccination',
                'personalDetails',
            ],
            array_keys($data['data']),
        );
        $this->assertCount(8, $responseDataValues);
    }

    public function testGetMultiNotFound(): void
    {
        $user = $this->createUser();

        $response = $this->be($user)->get('/api/tasks/non-existing-uuid/fragments/?names=general,circumstances');
        $response->assertStatus(404);
    }

    public function testPutSingleFragmentStorage(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $task = $this->createTaskForCase($case, [
            'created_at' => CarbonImmutable::now(),
        ]);

        // no fields required
        $response = $this->be($user)->putJson('/api/tasks/' . $task->uuid . '/fragments/general', []);
        $response->assertStatus(200);

        // check storage
        $response = $this->be($user)->putJson('/api/tasks/' . $task->uuid . '/fragments/general', [
            'firstname' => 'John',
        ]);
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertEquals('John', $data['data']['firstname']);

        // check if really stored
        $response = $this->be($user)->get('/api/tasks/' . $task->uuid . '/fragments/general');
        $data = $response->json();
        $this->assertEquals('John', $data['data']['firstname']);
    }

    public function testPutMultiFragmentStorage(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $task = $this->createTaskForCase($case, [
            'created_at' => CarbonImmutable::now(),
        ]);

        $response = $this->be($user)->putJson('/api/tasks/' . $task->uuid . '/fragments/', [
            'general' => [
                'firstname' => 'John',
            ],
            'circumstances' => [
                'wasUsingPPE' => YesNoUnknown::yes(),
            ],
        ]);
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertEquals('John', $data['data']['general']['firstname']);
        $this->assertEquals(YesNoUnknown::yes(), $data['data']['circumstances']['wasUsingPPE']);

        // check if really stored
        $response = $this->be($user)->get('/api/tasks/' . $task->uuid . '/fragments/?fragmentNames=general,circumstances');
        $data = $response->json();
        $this->assertEquals('John', $data['data']['general']['firstname']);
        $this->assertEquals(YesNoUnknown::yes(), $data['data']['circumstances']['wasUsingPPE']);
    }

    public function testNonAccessibleTaskShouldReturn404ForEachFragment(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $task = $this->createTaskForCase($case, [
            'created_at' => CarbonImmutable::now(),
            'general' => General::newInstanceWithVersion(1, static function (General $general): void {
                $general->firstname = 'John';
                $general->lastname = 'Snow';
                $general->email = 'john.snow@got.com';
                $general->phone = '0612345678';
            }),
        ]);

        $taskAvailablityInDays = 28;
        config()->set('misc.encryption.task_availability_in_days', $taskAvailablityInDays);
        CarbonImmutable::setTestNow(CarbonImmutable::now()->addDays($taskAvailablityInDays));

        $fragments = [
            'general',
            'inform',
            'alternativeLanguage',
            'circumstances',
            'symptoms',
            'test',
            'vaccination',
            'personalDetails',
            'alternateContact',
            'job',
        ];

        foreach ($fragments as $fragment) {
            $response = $this->be($user)->get('/api/tasks/' . $task->uuid . '/fragments/' . $fragment);
            $response->assertStatus(404);
            $response->assertSeeText('Deze fragment bestaat niet (meer)');
        }
    }
}
