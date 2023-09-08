<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Web;

use Carbon\CarbonImmutable;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\FeatureTestCase;

class ConversationCoachControllerTest extends FeatureTestCase
{
    #[DataProvider('indexPermissionsProvider')]
    public function testConversationCoachIndexPermissions(string $roles, int $expectedResponseStatusCode): void
    {
        $user = $this->createUser(['consented_at' => CarbonImmutable::now()], $roles);

        $this->be($user)->get('/gesprekscoach')->assertStatus($expectedResponseStatusCode);
    }

    public static function indexPermissionsProvider(): Generator
    {
        yield 'user' => ['user', 403];
        yield 'medical supervisor' => ['medical_supervisor', 403];
        yield 'medical supervisor nationwide' => ['medical_supervisor_nationwide', 403];
        yield 'conversation coach' => ['conversation_coach', 200];
        yield 'conversation coach nationwide' => ['conversation_coach_nationwide', 200];
    }
}
