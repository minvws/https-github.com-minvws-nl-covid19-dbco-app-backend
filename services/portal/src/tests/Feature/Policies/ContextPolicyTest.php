<?php

declare(strict_types=1);

namespace Tests\Feature\Policies;

use App\Policies\ContextPolicy;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function app;
use function array_merge;

#[Group('authorization')]
class ContextPolicyTest extends FeatureTestCase
{
    private ContextPolicy $contextPolicy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->contextPolicy = app(ContextPolicy::class);
    }

    public function testUserCanCreateContextOnCaseIfAssignedToCase(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);

        $this->be($user)->postJson('api/cases/' . $case->uuid . '/contexts', ['context' => ['foo']])->assertOk();
    }

    public function testUserCannotCreateContextOnCaseIfNotAssignedToCase(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $case->assigned_user_uuid = null;
        $case->save();

        $this->be($user)->postJson('api/cases/' . $case->uuid . '/contexts', ['context' => ['foo']])->assertStatus(403);
    }

    #[DataProvider('policyDataProviderAssignedToCase')]
    public function testCheckPolicyPermissionsUserAssignedToCase(
        string $role,
        string $action,
        bool $allowed,
    ): void {
        [$user, $context] = $this->createOwnedByOrganisationUserPlace($role);

        $this->assertEquals($allowed, $this->contextPolicy->$action($user, $context), "`{$role}` tries to access method `{$action}`");
    }

    #[DataProvider('policyDataProviderNotAssignedToCase')]
    public function testCheckPolicyPermissionsUserNotAssignedToCase(
        string $role,
        string $action,
        bool $allowed,
    ): void {
        [$user, $context] = $this->createNotOwnedByOrganisationUserPlace($role);

        $this->assertEquals($allowed, $this->contextPolicy->$action($user, $context), "`{$role}` tries to access method `{$action}`");
    }

    public static function policyDataProviderAssignedToCase(): Generator
    {
        $defaultPermissions = [
            'create' => false,
            'edit' => false,
            'delete' => false,
            'view' => false,
            'link' => false,
        ];

        foreach (
            array_merge($defaultPermissions, [
                'create' => true,
                'edit' => true,
                'delete' => true,
                'view' => true,
                'link' => true,
            ]) as $action => $allowed
        ) {
            yield 'A user with role `user` can perform `' . $action . '` on a Context when assigned to case' => [
                'user',
                $action,
                $allowed,
            ];
        }

        foreach (
            array_merge($defaultPermissions, [
                'create' => true,
                'edit' => true,
                'delete' => true,
                'view' => true,
                'link' => true,
            ]) as $action => $allowed
        ) {
            yield 'A user with role `user_nationwide` can perform `' . $action . '` on a Context when assigned to case' => [
                'user_nationwide',
                $action,
                $allowed,
            ];
        }

        foreach (
            array_merge($defaultPermissions, [
                'create' => true,
                'edit' => true,
                'delete' => true,
                'view' => true,
                'link' => true,
            ]) as $action => $allowed
        ) {
            yield 'A user with role `casequality` can perform `' . $action . '` on a Context when assigned to case' => [
                'casequality',
                $action,
                $allowed,
            ];
        }

        foreach ($defaultPermissions as $action => $allowed) {
            yield 'A user with role `contextmanager` can ' . ($allowed === false ? 'NOT' : '') . ' perform `' . $action . '` on a Context when assigned to case' => [
                'contextmanager',
                $action,
                $allowed,
            ];
        }

        foreach (
            array_merge($defaultPermissions, [
                'create' => true,
                'edit' => true,
                'delete' => true,
                'view' => true,
                'link' => true,
            ]) as $action => $allowed
        ) {
            yield 'A user with role `clusterspecialist` can ' . ($allowed === false ? 'NOT' : '') . ' perform `' . $action . '` on a Context when assigned to case' => [
                'clusterspecialist',
                $action,
                $allowed,
            ];
        }
    }

    public static function policyDataProviderNotAssignedToCase(): Generator
    {
        $defaultPermissions = [
            'create' => false,
            'edit' => false,
            'delete' => false,
            'view' => false,
            'link' => false,
        ];

        foreach (
            array_merge($defaultPermissions, [
                'create' => true,
                'link' => true,
            ]) as $action => $allowed
        ) {
            yield 'A user with role `user` can ' . ($allowed === false ? 'NOT' : '') . ' perform `' . $action . '` on a Context when not assigned to case' => [
                'user',
                $action,
                $allowed,
            ];
        }

        foreach (
            array_merge($defaultPermissions, [
                'create' => true,
                'link' => true,
            ]) as $action => $allowed
        ) {
            yield 'A user with role `user_nationwide` can ' . ($allowed === false ? 'NOT' : '') . ' perform `' . $action . '` on a Context when not assigned to case' => [
                'user_nationwide',
                $action,
                $allowed,
            ];
        }

        foreach (
            array_merge($defaultPermissions, [
                'create' => true,
                'link' => true,
            ]) as $action => $allowed
        ) {
            yield 'A user with role `casequality` can ' . ($allowed === false ? 'NOT' : '') . ' perform `' . $action . '` on a Context when not assigned to case' => [
                'casequality',
                $action,
                $allowed,
            ];
        }

        foreach ($defaultPermissions as $action => $allowed) {
            yield 'A user with role `contextmanager` can ' . ($allowed === false ? 'NOT' : '') . ' perform `' . $action . '` on a Context when not assigned to case' => [
                'contextmanager',
                $action,
                $allowed,
            ];
        }

        foreach (
            array_merge($defaultPermissions, [
                'create' => true,
                'link' => true,
            ]) as $action => $allowed
        ) {
            yield 'A user with role `clusterspecialist` can ' . ($allowed === false ? 'NOT' : '') . ' perform `' . $action . '` on a Context when not assigned to case' => [
                'clusterspecialist',
                $action,
                $allowed,
            ];
        }
    }

    private function createOwnedByOrganisationUserPlace(string $role): array
    {
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation, [], $role);

        $case = $this->createCaseForUser($user);
        $context = $this->createContextForCase($case);

        return [$user, $context];
    }

    private function createNotOwnedByOrganisationUserPlace(string $role): array
    {
        $organisationOne = $this->createOrganisation();
        $organisationTwo = $this->createOrganisation();

        $user = $this->createUserForOrganisation($organisationOne, [], $role);

        $case = $this->createCaseForOrganisation($organisationTwo);
        $context = $this->createContextForCase($case);

        return [$user, $context];
    }
}
