<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Assignment;

use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentUser;
use App\Services\Assignment\AssignmentJwtTokenService;
use App\Services\Assignment\AssignmentTokenService;
use App\Services\Assignment\Enum\AssignmentModelEnum;
use App\Services\Assignment\Exception\AssignmentRuntimeException;
use App\Services\Assignment\TokenResource;
use Carbon\CarbonImmutable;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;
use Throwable;

#[Group('assignment')]
final class AssignmentJwtTokenServiceTest extends FeatureTestCase
{
    private Config $config;
    private AssignmentJwtTokenService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->config = $this->app->make(Config::class);

        // To make the output of the service consistent regardless of whatever the value is set to for test env:
        $this->config->set('assignment.jwt.secret', 'MY_JWT_SECRET');

        $this->service = $this->app->make(AssignmentJwtTokenService::class);
    }

    public function testItCanBeInitialized(): void
    {
        $this->assertInstanceOf(AssignmentJwtTokenService::class, $this->service);
        $this->assertInstanceOf(AssignmentTokenService::class, $this->service);
    }

    public function testCreateTokenForCases(): void
    {
        $user = $this->createUserWithOrganisation();
        $uuids = $this->createCasesForUser($user, count: 5);

        $token = $this->service->createTokenForCases($uuids, $user);

        $this->assertIsString($token);
        $this->assertTrue($this->isValidToken($token), 'It did not return a valid token');
    }

    public function testCreateTokenForCasesWithMaxExpirationTtl(): void
    {
        $user = $this->createUserWithOrganisation();
        $uuids = $this->createCasesForUser($user, count: 5);

        $token = $this->service->createTokenForCases($uuids, $user, ttlExpirationInMinutes: 24 * 60);

        $this->assertIsString($token);
        $this->assertTrue($this->isValidToken($token), 'It did not return a valid token');
    }

    public function testTokenIsInvalidAfterExpirationTime(): void
    {
        $now = CarbonImmutable::now();
        CarbonImmutable::setTestNow($now->subMinutes(15));

        $user = $this->createUserWithOrganisation();
        $uuids = $this->createCasesForUser($user, count: 5);

        $token = $this->service->createTokenForCases($uuids, $user, ttlExpirationInMinutes: 10);

        CarbonImmutable::setTestNow();

        $this->assertIsString($token);
        $this->assertTrue($this->isExpiredToken($token), 'Token should be expired!');
    }

    public function testItThrowsARuntimeExceptionIfNumberOfCasesExceedsConfiguredMax(): void
    {
        $this->config->set('assignment.stateless.cases.max_uuids', 5);

        /** @var AssignmentJwtTokenService $service */
        $service = $this->app->make(AssignmentJwtTokenService::class);

        $user = $this->createUserWithOrganisation();
        $uuids = $this->createCasesForUser($user, count: 6);

        $this->expectExceptionObject(new AssignmentRuntimeException('Only allowed to pass "5" case uuids, given "6" case uuids.'));

        $service->createTokenForCases($uuids, $user);
    }

    public function testCreateToken(): void
    {
        $user = $this->createUserWithOrganisation();
        $uuids = $this->createCasesForUser($user, count: 5);

        $tokenResources = Collection::make(new TokenResource(mod: AssignmentModelEnum::Case_, ids: $uuids));

        $token = $this->service->createToken($tokenResources, $user);

        $this->assertIsString($token);
        $this->assertTrue($this->isValidToken($token), 'It did not return a valid token');
    }

    public function testCreateTokenWithMaxExpirationTtl(): void
    {
        $user = $this->createUserWithOrganisation();
        $uuids = $this->createCasesForUser($user, count: 5);

        $tokenResources = Collection::make(new TokenResource(mod: AssignmentModelEnum::Case_, ids: $uuids));

        $token = $this->service->createToken($tokenResources, $user, ttlExpirationInMinutes: 24 * 60);

        $this->assertIsString($token);
        $this->assertTrue($this->isValidToken($token), 'It did not return a valid token');
    }

    /**
     * @return array<int,string> Array of uuids
     */
    private function createCasesForUser(EloquentUser $user, int $count): array
    {
        $organisation = $user->organisations()->first();

        return EloquentCase::factory($count)
            ->create([
                'assigned_user_uuid' => $user->uuid,
                'organisation_uuid' => $organisation->uuid,
                'bco_phase' => $organisation->bcoPhase,
            ])
            ->pluck('uuid')
            ->toArray();
    }

    private function isValidToken(string $token): bool
    {
        try {
            JWT::decode($token, new Key($this->config->get('assignment.jwt.secret'), 'HS256'));
        } catch (Throwable $e) {
            return false;
        }

        return true;
    }

    private function isExpiredToken(string $token): bool
    {
        try {
            JWT::decode($token, new Key($this->config->get('assignment.jwt.secret'), 'HS256'));
        } catch (ExpiredException $e) {
            return true;
        } catch (Throwable $e) {
            return false;
        }

        return false;
    }
}
