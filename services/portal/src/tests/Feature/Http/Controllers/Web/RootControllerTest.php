<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Web;

use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\FeatureTestCase;

class RootControllerTest extends FeatureTestCase
{
    #[DataProvider('rootRedirectDataProvider')]
    public function testRootRedirect(string $roles, string $expectedRedirectUri): void
    {
        $user = $this->createUser([
            'consented_at' => CarbonImmutable::now(),
        ], $roles);

        $this->be($user)->get('/')->assertRedirect($expectedRedirectUri);
    }

    public static function rootRedirectDataProvider(): array
    {
        return [
            'user' => ['user', '/cases'],
            'user_nationwide' => ['user_nationwide', '/cases'],
            'planner' => ['planner', '/planner'],
            'planner_nationwide' => ['planner_nationwide', '/planner'],
            'user,planner' => ['user,planner', '/cases'],
            'compliance' => ['compliance', '/compliance'],
            'user,compliance' => ['user,compliance', '/cases'],
            'user,contextmanager' => ['user,contextmanager', '/cases'],
            'clusterspecialist' => ['clusterspecialist', '/places'],
            'user,clusterspecialist' => ['user,clusterspecialist', '/cases'],
            'casequality' => ['casequality', '/cases'],
            'casequality_nationwide' => ['casequality_nationwide', '/cases'],
            'medical_supervisor' => ['medical_supervisor', '/medische-supervisie'],
            'medical_supervisor_nationwide' => ['medical_supervisor_nationwide', '/medische-supervisie'],
            'conversation_coach' => ['conversation_coach', '/gesprekscoach'],
            'conversation_coach_nationwide' => ['conversation_coach_nationwide', '/gesprekscoach'],
            'medical_supervisor,conversation_coach' => ['medical_supervisor,conversation_coach', '/medische-supervisie'],
            'callcenter,callcenter_expert' => ['callcenter,callcenter_expert', '/dossierzoeken'],
            'admin' => ['admin', '/beheren'],
        ];
    }
}
