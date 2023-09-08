<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Assignment;

use App\Models\Eloquent\EloquentUser;
use App\Services\Assignment\AssignmentJwtTokenAuthService;
use App\Services\Assignment\AssignmentJwtTokenService;
use App\Services\Assignment\AssignmentTokenAuthService;
use App\Services\Assignment\Enum\AssignmentModelEnum;
use App\Services\Assignment\Exception\AssignmentException;
use App\Services\Assignment\Exception\AssignmentRuntimeException;
use App\Services\Assignment\Exception\Http\AssignmentExpiredTokenHttpException;
use App\Services\Assignment\Exception\Http\AssignmentInvalidTokenHttpException;
use App\Services\Assignment\Token;
use App\Services\Assignment\TokenResource;
use Carbon\CarbonImmutable;
use Firebase\JWT\JWT;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

#[Group('assignment')]
final class AssignmentJwtTokenAuthServiceTest extends FeatureTestCase
{
    private const JWT_SECRET = 'MY_JWT_SECRET';

    private Config $config;

    protected function setUp(): void
    {
        parent::setUp();

        $this->config = $this->app->make(Config::class);

        $this->config->set('assignment.jwt.secret', self::JWT_SECRET);
    }

    public function testItCanBeInitialized(): void
    {
        $service = $this->app->make(AssignmentJwtTokenAuthService::class);

        $this->assertInstanceOf(AssignmentJwtTokenAuthService::class, $service);
        $this->assertInstanceOf(AssignmentTokenAuthService::class, $service);
    }

    public function testHasTokenWithRequestWithToken(): void
    {
        $this->setTokenOnRequest($this->faker->word());

        /** @var AssignmentJwtTokenAuthService $service */
        $service = $this->app->make(AssignmentJwtTokenAuthService::class);

        $this->assertTrue($service->hasToken(), 'Request should have a token!');
    }

    public function testHasTokenWithRequestWithoutToken(): void
    {
        /** @var AssignmentJwtTokenAuthService $service */
        $service = $this->app->make(AssignmentJwtTokenAuthService::class);

        $this->assertFalse($service->hasToken(), 'Request should NOT have a token!');
    }

    public function testGetTokenWithRequestWithValidToken(): void
    {
        /** @var AssignmentJwtTokenAuthService $service */
        $service = $this->app->make(AssignmentJwtTokenAuthService::class);

        $user = $this->createUserWithOrganisation();

        $this->setTokenOnRequest($this->getValidToken(['uuid_one'], $user), $user);

        $token = $service->getToken();

        $this->assertInstanceOf(Token::class, $token);
        $this->assertSame($token->sub, $user->uuid);
    }

    public function testGetTokenWithRequestWithoutToken(): void
    {
        /** @var AssignmentJwtTokenAuthService $service */
        $service = $this->app->make(AssignmentJwtTokenAuthService::class);

        $this->expectExceptionObject(new AssignmentRuntimeException('Failed fetching token header'));

        $service->getToken();
    }

    public function testGetTokenWithRequestWithExpiredToken(): void
    {
        $user = $this->createUserWithOrganisation();

        $expiredToken = CarbonImmutable::withTestNow(
            CarbonImmutable::now()->subMinutes(30),
            fn (): string => $this->getValidToken(
                caseUuids: [$this->faker->uuid()],
                user: $user,
                ttlExpirationInMinutes: 10,
            ),
        );
        $this->setTokenOnRequest($expiredToken);

        /** @var AssignmentJwtTokenAuthService $service */
        $service = $this->app->make(AssignmentJwtTokenAuthService::class);

        $this->expectExceptionObject(new AssignmentExpiredTokenHttpException());

        $service->getToken();
    }

    public function testGetTokenWithRequestWithInvalidToken(): void
    {
        $differentJwtKey = $this->faker->words(asText: true);
        $token = JWT::encode(['sub' => $this->faker->randomNumber()], $differentJwtKey, 'HS256');
        $this->setTokenOnRequest($token);

        /** @var AssignmentJwtTokenAuthService $service */
        $service = $this->app->make(AssignmentJwtTokenAuthService::class);

        $this->expectExceptionObject(new AssignmentInvalidTokenHttpException());

        $service->getToken();
    }

    public function testGetTokenWithRequestWithInvalidFormattedToken(): void
    {
        $token = JWT::encode(['sub' => $this->faker->randomNumber()], self::JWT_SECRET, 'HS256');
        $this->setTokenOnRequest($token);

        /** @var AssignmentJwtTokenAuthService $service */
        $service = $this->app->make(AssignmentJwtTokenAuthService::class);

        $this->expectExceptionObject(new AssignmentInvalidTokenHttpException());

        $service->getToken();
    }

    public function testAllowedWithRequestWithValidToken(): void
    {
        $user = $this->createUserWithOrganisation();

        $validToken = $this->getValidToken(caseUuids: ['uuid_one', 'uuid_two'], user: $user);
        $this->setTokenOnRequest($validToken);

        /** @var AssignmentJwtTokenAuthService $service */
        $service = $this->app->make(AssignmentJwtTokenAuthService::class);

        $result = $service->allowed(AssignmentModelEnum::Case_, ['uuid_one', 'uuid_two'], $user);

        $this->assertTrue($result, 'All uuids should be allowed');
    }

    public function testAllowedWithRequestWithoutToken(): void
    {
        $user = $this->createUserWithOrganisation();

        /** @var AssignmentJwtTokenAuthService $service */
        $service = $this->app->make(AssignmentJwtTokenAuthService::class);

        $result = $service->allowed(AssignmentModelEnum::Case_, [$this->faker->word()], $user);

        $this->assertFalse($result, 'Should not be allowed because request has no token!');
    }

    public function testAllowedWithDifferentUser(): void
    {
        $user = $this->createUserWithOrganisation();
        $user2 = $this->createUserWithOrganisation();

        $validToken = $this->getValidToken(caseUuids: ['uuid_one', 'uuid_two'], user: $user);
        $this->setTokenOnRequest($validToken);

        /** @var AssignmentJwtTokenAuthService $service */
        $service = $this->app->make(AssignmentJwtTokenAuthService::class);

        $result = $service->allowed(AssignmentModelEnum::Case_, ['uuid_one', 'uuid_two'], $user2);

        $this->assertFalse($result, 'Token subject should not match the given user');
    }

    public function testAllowedCases(): void
    {
        $user = $this->createUserWithOrganisation();

        $validToken = $this->getValidToken(caseUuids: ['uuid_one', 'uuid_two'], user: $user);
        $this->setTokenOnRequest($validToken);

        /** @var AssignmentJwtTokenAuthService $service */
        $service = $this->app->make(AssignmentJwtTokenAuthService::class);

        $result = $service->allowedCases(['uuid_one', 'uuid_two'], $user);

        $this->assertTrue($result, 'All uuids should be allowed');
    }

    private function getValidToken(
        array $caseUuids,
        EloquentUser $user,
        int $ttlExpirationInMinutes = 10,
    ): string {
        $tokenResources = Collection::make([new TokenResource(mod: AssignmentModelEnum::Case_, ids: $caseUuids)])
            ->add(new TokenResource(mod: AssignmentModelEnum::Case_, ids: ['added_uuid_one', 'added_uuid_two']));

        try {
            /** @var AssignmentJwtTokenService $generateToken */
            $generateToken = $this->app->make(AssignmentJwtTokenService::class);

            return $generateToken->createToken($tokenResources, $user, $ttlExpirationInMinutes);
        } catch (AssignmentException) {
            throw new AssertionFailedError('Something went wrong getting a valid token!');
        }
    }

    private function setTokenOnRequest(string $token): void
    {
        /** @var Request $request */
        $request = $this->app->make(Request::class);
        $request->headers->set(
            $this->config->get('assignment.token_fetcher.request_header.header_name'),
            $token,
        );
    }
}
