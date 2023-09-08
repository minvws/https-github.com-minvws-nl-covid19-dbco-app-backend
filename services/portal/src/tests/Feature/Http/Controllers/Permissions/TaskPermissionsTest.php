<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Permissions;

use App\Models\Eloquent\EloquentTask;
use App\Models\Eloquent\EloquentUser;
use Generator;
use Illuminate\Http\Response;
use PHPUnit\Framework\Attributes\DataProvider;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Request;
use Tests\Feature\FeatureTestCase;

use function str_replace;
use function trim;

class TaskPermissionsTest extends FeatureTestCase
{
    private EloquentUser $userAssignedToCase;
    private EloquentUser $userNotAssignedToCase;
    private EloquentTask $task;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpScenarios();
    }

    public function testUserCannotDeleteTaskIfNotAssignedToCase(): void
    {
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation);
        $case = $this->createCaseForOrganisation($organisation);

        $task = $this->createTaskForCase($case);

        $this->be($user)->deleteJson('api/tasks/' . $task->uuid)->assertStatus(403);
    }

    public function testUserCannotCreateTaskIfNotAssignedToCase(): void
    {
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation);
        $case = $this->createCaseForOrganisation($organisation);

        $this->be($user)->postJson(
            'api/cases/' . $case->uuid . '/tasks',
            ['task' => ['group' => 'contact', 'label' => 'henk']],
        )->assertStatus(403);
    }

    #[DataProvider('taskEditPermissionsProvider')]
    public function testTaskEditPermissions(string $url, string $method, array $payload, string $role, bool $allowed): void
    {
        $url = str_replace('{task}', $this->task->uuid, $url);

        /** @var Response $response */
        $response = $this->be($this->{$role})->json($method, $url, $payload);

        $errorString = 'For ' . $role . ' on ' . $method . '::' . $url;
        if ($allowed) {
            $this->assertSame(200, $response->getStatusCode(), $errorString);
        } else {
            $this->assertSame(403, $response->getStatusCode(), $errorString);
        }
    }

    public static function taskEditPermissionsProvider(): Generator
    {
        foreach (
            [
                'userAssignedToCase' => true,
                'userNotAssignedToCase' => false,
            ] as $actor => $allowed
        ) {
            yield self::makeScenarioName("Scenario $actor can update task", $allowed) => [
                'api/tasks/{task}',
                Request::METHOD_PUT,
                [],
                $actor,
                $allowed,
            ];

            yield self::makeScenarioName("Scenario $actor can get connected tasks", $allowed) => [
                'api/tasks/{task}/connected',
                Request::METHOD_GET,
                [],
                $actor,
                $allowed,
            ];

            yield self::makeScenarioName("Scenario $actor can update task fragments", $allowed) => [
                'api/tasks/{task}/fragments',
                Request::METHOD_PUT,
                ['general' => []],
                $actor,
                $allowed,
            ];

            yield self::makeScenarioName("Scenario $actor can update task specific fragment", $allowed) => [
                'api/tasks/{task}/fragments/general',
                Request::METHOD_PUT,
                [],
                $actor,
                $allowed,
            ];
        }
    }

    private function setupScenarios(): void
    {
        $organisation = $this->createOrganisation();

        $this->userAssignedToCase = $this->createUserForOrganisation($organisation);
        $this->userNotAssignedToCase = $this->createUserForOrganisation($organisation);

        $this->task = $this->createTaskForUser($this->userAssignedToCase, ['pseudo_bsn_guid' => Uuid::uuid4()->toString()]);
    }

    private static function makeScenarioName(string $scenario, bool $allowed): string
    {
        return trim($scenario) . ': ' . ($allowed ? 'true' : 'false');
    }
}
