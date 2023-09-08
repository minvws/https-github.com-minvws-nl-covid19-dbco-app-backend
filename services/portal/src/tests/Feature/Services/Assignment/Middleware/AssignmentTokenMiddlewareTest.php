<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Assignment\Middleware;

use App\Models\Eloquent\EloquentUser;
use App\Services\Assignment\AssignmentTokenService;
use App\Services\Assignment\Middleware\AssignmentTokenMiddleware;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Http\Kernel as KernelContract;
use Illuminate\Foundation\Http\Kernel;
use Illuminate\Routing\Router;
use MinVWS\Audit\Services\AuditService;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function assert;

#[Group('assignment')]
class AssignmentTokenMiddlewareTest extends FeatureTestCase
{
    private string $testEndpoint = 'assignment-token-middleware-test';
    private string $assignmentHeader;

    protected function setUp(): void
    {
        parent::setUp();

        $config = $this->app->make(Config::class);
        $this->assignmentHeader = $config->get('assignment.token_fetcher.request_header.header_name');
    }

    public function testItCanbeInitialized(): void
    {
        /** @var AssignmentTokenMiddleware $middleware */
        $middleware = $this->app->make(AssignmentTokenMiddleware::class);

        $this->assertInstanceOf(AssignmentTokenMiddleware::class, $middleware);
    }

    public function testAssignmentTokenMiddlewareIsAddedToApiGroup(): void
    {
        /** @var KernelContract&Kernel $kernel */
        $kernel = $this->app->make(KernelContract::class);

        $this->assertTrue(isset($kernel->getMiddlewareGroups()['api']), 'Middlewaregroup api should exist!');
        $this->assertContains(AssignmentTokenMiddleware::class, $kernel->getMiddlewareGroups()['api']);
    }

    public function testAuthUserHasToken(): void
    {
        $user = $this->createUserWithOrganisation();
        $this->be($user);

        /** @var AssignmentTokenService $tokenService */
        $tokenService = $this->app->make(AssignmentTokenService::class);

        $token = $tokenService->createTokenForCases([$this->faker->uuid(), $this->faker->uuid()], $user);

        $this
            ->setupTestEndpoint()
            ->getJson($this->testEndpoint, headers: [$this->assignmentHeader => $token]);

        /** @var Guard $guard */
        $guard = $this->app->make(Guard::class);

        $guardUser = $guard->user();
        assert($guardUser instanceof EloquentUser);

        $this->assertSame($user, $guardUser);
        $this->assertTrue($user->hasToken(), 'User should have a token set.');
        $this->assertSame($user->getToken(), $guardUser->getToken());
    }

    public function testItsANoOpWhenNoUserIsAuthenticated(): void
    {
        $user = $this->createUserWithOrganisation();

        $this
            ->setupTestEndpoint()
            ->getJson($this->testEndpoint);

        $this->assertFalse($user->hasToken(), 'User should not have a token.');
    }

    public function testAuthUserOnlyGetsHisOwnTokensAdded(): void
    {
        $user1 = $this->createUserWithOrganisation();
        $user2 = $this->createUserWithOrganisation();

        $this->be($user1);

        /** @var AssignmentTokenService $tokenService */
        $tokenService = $this->app->make(AssignmentTokenService::class);

        $token = $tokenService->createTokenForCases([$this->faker->uuid(), $this->faker->uuid()], $user2);

        $this
            ->setupTestEndpoint()
            ->getJson($this->testEndpoint, [$this->assignmentHeader => $token]);

        $this->assertFalse($user1->hasToken(), 'User1 should not have a token!');
        $this->assertFalse($user2->hasToken(), 'User2 should not have a token!');
    }

    private function setupTestEndpoint(): self
    {
        /** @var Router $router */
        $router = $this->app->make(Router::class);
        $router
            ->middleware(AssignmentTokenMiddleware::class)
            ->get($this->testEndpoint, static function (AuditService $auditService): array {
                $auditService->setEventExpected(false);

                return [];
            });

        return $this;
    }
}
