<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\FeatureTestCase;

use function route;

class CaseMetricsControllerTest extends FeatureTestCase
{
    #[DataProvider('provideRolesAndIsAccessible')]
    public function testListCaseMetricsPermissions(string $roles, bool $isAccessible): void
    {
        $user = $this->createUser([], $roles);
        $response = $this->be($user)->get(route('case-metrics'));

        $isAccessible
            ? $response->assertOk()
            : $response->assertForbidden();
    }

    public static function provideRolesAndIsAccessible(): Generator
    {
        yield '`planner` role' => ['planner', true];
        yield '`planner_nationwide` role' => ['planner_nationwide', false];
        yield '`user` role' => ['user', false];
        yield '`conversation_coach` role' => ['conversation_coach', false];
        yield '`medical_supervisor` role' => ['medical_supervisor', false];
    }
}
