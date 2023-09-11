<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Web;

use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\FeatureTestCase;

class PlannerControllerTest extends FeatureTestCase
{
    #[DataProvider('rolesDataProvider')]
    public function testListCasesAuthorization(string $roles, int $expectedResponseStatusCode): void
    {
        $user = $this->createUser(['consented_at' => CarbonImmutable::now()], $roles);

        $response = $this->be($user)->get('/planner');
        $this->assertEquals($expectedResponseStatusCode, $response->getStatusCode());
    }

    public static function rolesDataProvider(): array
    {
        return [
            'user' => ['user', 403],
            'planner' => ['planner', 200],
            'user,planner' => ['user,planner', 200],
        ];
    }
}
