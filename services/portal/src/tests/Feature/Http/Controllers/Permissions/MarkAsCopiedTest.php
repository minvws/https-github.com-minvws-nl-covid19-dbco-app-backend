<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Permissions;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\Feature\FeatureTestCase;

class MarkAsCopiedTest extends FeatureTestCase
{
    #[DataProvider('userRoles')]
    #[TestDox('User with role $role is allowed to mark cases as copied when assigned to case')]
    public function testCopyCaseForAssignedUserIsAllowedPermissionMatrix(string $role): void
    {
        $userWithCaseEditRights = $this->createUser([], $role);
        $case = $this->createCaseForUser($userWithCaseEditRights);

        $response = $this->be($userWithCaseEditRights)->postJson('api/markascopied', [
            'caseId' => $case->uuid,
            'fieldName' => 'Test',
        ]);
        $this->assertSame(200, $response->getStatusCode());
    }

    #[DataProvider('userRoles')]
    #[TestDox('User with role $role is allowed to mark tasks as exported when assigned to case')]
    public function testCopyCaseAndTaskForAssignedUserIsAllowedPermissionMatrix(string $role): void
    {
        $userWithCaseEditRights = $this->createUser([], $role);
        $case = $this->createCaseForUser($userWithCaseEditRights);
        $task = $this->createTaskForUser($userWithCaseEditRights);

        $response = $this->be($userWithCaseEditRights)->postJson('api/markascopied', [
            'caseId' => $case->uuid,
            'taskId' => $task->uuid,
            'fieldName' => 'Test',
        ]);
        $this->assertSame(200, $response->getStatusCode());
    }

    #[DataProvider('userRoles')]
    #[TestDox('User with role $role is NOT allowed to mark cases as exported when NOT assigned to case')]
    public function testCopyCaseForUnassignedUserIsNotAllowedPermissionMatrix(string $role): void
    {
        $userWithCaseEditRights = $this->createUser();
        $userWithoutCaseEditRights = $this->createUserForOrganisation(
            $userWithCaseEditRights->getRequiredOrganisation(),
            [],
            $role,
        );
        $case = $this->createCaseForUser($userWithCaseEditRights);

        $response = $this->be($userWithoutCaseEditRights)->postJson('api/markascopied', [
            'caseId' => $case->uuid,
            'fieldName' => 'Test',
        ]);
        $this->assertSame(403, $response->getStatusCode());
    }

    #[DataProvider('userRoles')]
    #[TestDox('User with role $role is NOT allowed to mark tasks as exported when NOT supplying a case id')]
    public function testCopyTaskForUnassignedUserIsNotAllowedPermissionMatrix(string $role): void
    {
        $userWithCaseEditRights = $this->createUser();
        $userWithoutCaseEditRights = $this->createUserForOrganisation(
            $userWithCaseEditRights->getRequiredOrganisation(),
            [],
            $role,
        );
        $task = $this->createTaskForUser($userWithCaseEditRights);

        $response = $this->be($userWithoutCaseEditRights)->postJson('api/markascopied', [
            'taskId' => $task->uuid,
            'fieldName' => 'Test',
        ]);
        $this->assertSame(422, $response->getStatusCode());
    }

    public static function userRoles(): array
    {
        return [
            ['user'],
            ['user_nationwide'],
            ['casequality'],
            ['casequality_nationwide'],
        ];
    }

    #[TestDox('User with role planner is NEVER allowed to mark cases as exported')]
    public function testCopyCaseAndTaskForAssignedPlannerIsNotAllowedPermissionMatrix(): void
    {
        $userWithCaseEditRights = $this->createUser([], 'planner');
        $case = $this->createCaseForUser($userWithCaseEditRights);
        $task = $this->createTaskForUser($userWithCaseEditRights);

        $response = $this->be($userWithCaseEditRights)->postJson('api/markascopied', [
            'caseId' => $case->uuid,
            'taskId' => $task->uuid,
            'fieldName' => 'Test',
        ]);
        $this->assertSame(403, $response->getStatusCode());
    }
}
