<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Permissions;

use App\Models\Eloquent\EloquentTask;
use App\Models\Eloquent\EloquentUser;
use Carbon\CarbonImmutable;
use Generator;
use Illuminate\Http\Response;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Request;
use Tests\Feature\FeatureTestCase;

use function str_replace;

class CompliancePermissionTest extends FeatureTestCase
{
    private EloquentUser $ownerComplianceUser;
    private EloquentUser $otherComplianceUser;
    private EloquentUser $assignedBcoUser;
    private EloquentUser $otherBcoUser;
    private EloquentTask $task;
    private EloquentTask $softDeletedTask;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpScenarios();
    }

    public function testSearchShouldFindCasesFromOwnerRegion(): void
    {
        $response = $this->be($this->ownerComplianceUser)->postJson('api/search', ['caseUuid' => $this->task->covidCase->uuid]);
        $this->assertSame($this->task->covidCase->uuid, $response->json()['cases'][0]['uuid']);
    }

    public function testSearchShouldNotFindCasesFromOtherRegion(): void
    {
        // Set caseUuid in a variable because after $this->be() the Case is no longer accessible because of CaseAuthScope...
        $caseUuid = $this->task->covidCase->uuid;

        $response = $this->be($this->otherComplianceUser)->postJson('api/search', ['caseUuid' => $caseUuid]);
        $this->assertEmpty($response->json()['cases']);
    }

    public function testSearchShouldFindTasksFromOwnerRegion(): void
    {
        $response = $this->be($this->ownerComplianceUser)->postJson('api/search', ['taskUuid' => $this->task->uuid]);
        $this->assertSame($this->task->uuid, $response->json()['contacts'][0]['uuid']);
    }

    public function testSearchShouldNotFindTasksFromOtherRegion(): void
    {
        $response = $this->be($this->otherComplianceUser)->postJson('api/search', ['taskUuid' => $this->task->uuid]);
        $this->assertEmpty($response->json()['contacts']);
    }

    #[DataProvider('complianceActions')]
    public function testCompliancePermissionMatrix(string $url, string $method, string $role, bool $allowed): void
    {
        $url = str_replace(
            ['{task}', '{softDeletedTask}', '{case}'],
            [$this->task->uuid, $this->softDeletedTask->uuid, $this->task->covidCase->uuid],
            $url,
        );

        /** @var Response $response */
        $response = $this->be($this->{$role})->{$method}($url);

        $errorString = 'For ' . $role . ' on ' . $method . '::' . $url;
        if ($allowed) {
            $this->assertSame(200, $response->getStatusCode(), $errorString);
        } else {
            // When the user is not logged in CaseAuthScope, 404 is returned ... Only 403 should be returned after CaseAuthScope is removed
            $this->assertContains($response->getStatusCode(), [403, 404], $errorString);
        }
    }

    public static function complianceActions(): Generator
    {
        foreach (
            [
                'ownerComplianceUser' => true,
                'otherComplianceUser' => false,
                'assignedBcoUser' => false,
                'otherBcoUser' => false,
            ] as $actor => $allowed
        ) {
            yield 'Delete case ' . $actor . ': ' . ($allowed ? 'true' : 'false') => [
                'api/access-requests/case/{case}',
                Request::METHOD_DELETE,
                $actor,
                $allowed,
            ];
        }

        foreach (
            [
                'ownerComplianceUser' => true,
                'otherComplianceUser' => false,
                'assignedBcoUser' => false,
                'otherBcoUser' => false,
            ] as $actor => $allowed
        ) {
            yield 'Download case PDF ' . $actor . ': ' . ($allowed ? 'true' : 'false') => [
                'api/access-requests/case/{case}/download',
                Request::METHOD_GET,
                $actor,
                $allowed,
            ];
        }

        foreach (
            [
                'ownerComplianceUser' => true,
                'otherComplianceUser' => false,
                'assignedBcoUser' => false,
                'otherBcoUser' => false,
            ] as $actor => $allowed
        ) {
            yield 'Download case HTML ' . $actor . ': ' . ($allowed ? 'true' : 'false') => [
                'api/access-requests/case/{case}/download/html',
                Request::METHOD_GET,
                $actor,
                $allowed,
            ];
        }

        foreach (
            [
                'ownerComplianceUser' => true,
                'otherComplianceUser' => false,
                'assignedBcoUser' => false,
                'otherBcoUser' => false,
            ] as $actor => $allowed
        ) {
            yield 'Get case fragments' . $actor . ': ' . ($allowed ? 'true' : 'false') => [
                'api/access-requests/case/{case}/fragments',
                Request::METHOD_GET,
                $actor,
                $allowed,
            ];
        }

        foreach (
            [
                'ownerComplianceUser' => true,
                'otherComplianceUser' => false,
                'assignedBcoUser' => false,
                'otherBcoUser' => false,
            ] as $actor => $allowed
        ) {
            yield 'Restore case ' . $actor . ': ' . ($allowed ? 'true' : 'false') => [
                'api/access-requests/case/{case}/restore',
                Request::METHOD_POST,
                $actor,
                $allowed,
            ];
        }

        foreach (
            [
                'ownerComplianceUser' => true,
                'otherComplianceUser' => false,
                'assignedBcoUser' => false,
                'otherBcoUser' => false,
            ] as $actor => $allowed
        ) {
            yield 'Get softdeleted task fragments ' . $actor . ': ' . ($allowed ? 'true' : 'false') => [
                'api/access-requests/task/{softDeletedTask}/fragments',
                Request::METHOD_GET,
                $actor,
                $allowed,
            ];
        }

        foreach (
            [
                'ownerComplianceUser' => true,
                'otherComplianceUser' => false,
                'assignedBcoUser' => false,
                'otherBcoUser' => false,
            ] as $actor => $allowed
        ) {
            yield 'Restore task ' . $actor . ': ' . ($allowed ? 'true' : 'false') => [
                'api/access-requests/task/{softDeletedTask}/restore',
                Request::METHOD_POST,
                $actor,
                $allowed,
            ];
        }

        foreach (
            [
                'ownerComplianceUser' => true,
                'otherComplianceUser' => false,
                'assignedBcoUser' => true,
                'otherBcoUser' => false,
            ] as $actor => $allowed
        ) {
            yield 'Delete task ' . $actor . ': ' . ($allowed ? 'true' : 'false') => [
                'api/access-requests/task/{task}',
                Request::METHOD_DELETE,
                $actor,
                $allowed,
            ];
        }

        foreach (
            [
                'ownerComplianceUser' => true,
                'otherComplianceUser' => false,
                'assignedBcoUser' => false,
                'otherBcoUser' => false,
            ] as $actor => $allowed
        ) {
            yield 'Download task PDF ' . $actor . ': ' . ($allowed ? 'true' : 'false') => [
                'api/access-requests/task/{task}/download',
                Request::METHOD_GET,
                $actor,
                $allowed,
            ];
        }

        foreach (
            [
                'ownerComplianceUser' => true,
                'otherComplianceUser' => false,
                'assignedBcoUser' => false,
                'otherBcoUser' => false,
            ] as $actor => $allowed
        ) {
            yield 'Download task HTML ' . $actor . ': ' . ($allowed ? 'true' : 'false') => [
                'api/access-requests/task/{task}/download/html',
                Request::METHOD_GET,
                $actor,
                $allowed,
            ];
        }

        foreach (
            [
                'ownerComplianceUser' => true,
                'otherComplianceUser' => true,
                'assignedBcoUser' => false,
                'otherBcoUser' => false,
            ] as $actor => $allowed
        ) {
            yield 'Compliance search query ' . $actor . ': ' . ($allowed ? 'true' : 'false') => [
                'api/search',
                Request::METHOD_POST,
                $actor,
                $allowed,
            ];
        }

        foreach (
            [
                'ownerComplianceUser' => true,
                'otherComplianceUser' => true,
                'assignedBcoUser' => false,
                'otherBcoUser' => false,
            ] as $actor => $allowed
        ) {
            yield 'View compliance dashboard page ' . $actor . ': ' . ($allowed ? 'true' : 'false') => [
                'compliance',
                Request::METHOD_GET,
                $actor,
                $allowed,
            ];
        }

        foreach (
            [
                'ownerComplianceUser' => true,
                'otherComplianceUser' => true,
                'assignedBcoUser' => false,
                'otherBcoUser' => false,
            ] as $actor => $allowed
        ) {
            yield 'View compliance search result page ' . $actor . ': ' . ($allowed ? 'true' : 'false') => [
                '/compliance/search',
                Request::METHOD_GET,
                $actor,
                $allowed,
            ];
        }
    }

    private function setUpScenarios(): void
    {
        $this->ownerComplianceUser = $this->createUser(
            [
                'name' => 'Owning organisation Compliance Officer',
                'consented_at' => CarbonImmutable::yesterday(),
            ],
            'compliance',
        );

        $this->assignedBcoUser = $this->createUserForOrganisation(
            $this->ownerComplianceUser->getRequiredOrganisation(),
            [
                'name' => 'Owner BCO User',
                'consented_at' => CarbonImmutable::yesterday(),
            ],
        );

        $this->otherBcoUser = $this->createUserForOrganisation(
            $this->ownerComplianceUser->getRequiredOrganisation(),
            [
                'name' => 'Other BCO user',
                'consented_at' => CarbonImmutable::yesterday(),
            ],
        );

        $case = $this->createCaseForUser($this->assignedBcoUser, [
            'created_at' => CarbonImmutable::now(),
        ]);

        $this->task = $this->createTaskForCase($case, ['created_at' => CarbonImmutable::now()]);
        $this->softDeletedTask = $this->createTaskForCase(
            $case,
            [
                'created_at' => CarbonImmutable::yesterday(),
                'deleted_at' => CarbonImmutable::now(),
            ],
        );

        // Other compliance user
        $this->otherComplianceUser = $this->createUser(
            [
                'name' => 'Other organisation Compliance Officer',
                'consented_at' => CarbonImmutable::yesterday(),
            ],
            'compliance',
        );
    }
}
