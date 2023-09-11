<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Web;

use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\FeatureTestCase;

use function route;

class UserControllerTest extends FeatureTestCase
{
    #[DataProvider('provideUserRoles')]
    public function testProfile(string $role, int $statusCode): void
    {
        if (!empty($role)) {
            $user = $this->createUser([], $role);
            $this->be($user);
        }

        $response = $this->get(route('user-profile'));

        $this->assertEquals($statusCode, $response->getStatusCode());
    }

    public static function provideUserRoles(): Generator
    {
        yield 'unauthenticated guest should redirect to login' => ['', 302];
        yield 'user should return ok' => ['user', 200];
    }
}
