<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Assignment;

use App\Models\Eloquent\EloquentUser;
use App\Services\Assignment\AssignmentJwtTokenService;
use App\Services\Assignment\Exception\AssignmentInvalidValueException;
use App\Services\Assignment\Exception\AssignmentRuntimeException;
use App\Services\Assignment\TokenEncoder\TokenEncoder;
use Illuminate\Config\Repository as Config;
use Illuminate\Support\Collection;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;

#[Group('assignment')]
final class AssignmentJwtTokenServiceTest extends UnitTestCase
{
    public function testItCanBeInitialized(): void
    {
        /** @var TokenEncoder&MockInterface $tokenEncoder */
        $tokenEncoder = Mockery::mock(TokenEncoder::class);

        $service = new AssignmentJwtTokenService($this->getConfigFixture(), $tokenEncoder);

        $this->assertInstanceOf(AssignmentJwtTokenService::class, $service);
    }

    public function testItThrowsAnAssignmentInvalidValueExceptionWhenTypeOfIssuerIsNotString(): void
    {
        $config = $this->getConfigFixture();

        /** @var TokenEncoder&MockInterface $tokenEncoder */
        $tokenEncoder = Mockery::mock(TokenEncoder::class);

        $config->set('assignment.jwt.issuer', null);

        $this->expectExceptionObject(
            new AssignmentInvalidValueException('Invalid type for value "issuer" given. Expected a "string", but got a "NULL"'),
        );

        new AssignmentJwtTokenService($config, $tokenEncoder);
    }

    public function testItThrowsAnAssignmentInvalidValueExceptionWhenTypeOfAudienceIsNotString(): void
    {
        $config = $this->getConfigFixture();

        /** @var TokenEncoder&MockInterface $tokenEncoder */
        $tokenEncoder = Mockery::mock(TokenEncoder::class);

        $config->set('assignment.jwt.audience', null);

        $this->expectExceptionObject(
            new AssignmentInvalidValueException('Invalid type for value "audience" given. Expected a "string", but got a "NULL"'),
        );

        new AssignmentJwtTokenService($config, $tokenEncoder);
    }

    public function testItThrowsAnAssignmentInvalidValueExceptionWhenTypeOfMaxCaseUuidsIsNotInteger(): void
    {
        $config = $this->getConfigFixture();

        /** @var TokenEncoder&MockInterface $tokenEncoder */
        $tokenEncoder = Mockery::mock(TokenEncoder::class);

        $config->set('assignment.stateless.cases.max_uuids', null);

        $this->expectExceptionObject(
            new AssignmentInvalidValueException('Invalid type for value "maxCaseUuids" given. Expected a "integer", but got a "NULL"'),
        );

        new AssignmentJwtTokenService($config, $tokenEncoder);
    }

    public function testgetAudience(): void
    {
        /** @var TokenEncoder&MockInterface $tokenEncoder */
        $tokenEncoder = Mockery::mock(TokenEncoder::class);

        $service = new AssignmentJwtTokenService($this->getConfigFixture(), $tokenEncoder);

        $this->assertSame('my_audience', $service->getAudience());
    }

    public function testGetIssuer(): void
    {
        /** @var TokenEncoder&MockInterface $tokenEncoder */
        $tokenEncoder = Mockery::mock(TokenEncoder::class);

        $service = new AssignmentJwtTokenService($this->getConfigFixture(), $tokenEncoder);

        $this->assertSame('my_issuer', $service->getIssuer());
    }

    public function testItThrowsAnExceptionWhenExpirationTtlIsToLongWhenCallingCreateToken(): void
    {
        /** @var TokenEncoder&MockInterface $tokenEncoder */
        $tokenEncoder = Mockery::mock(TokenEncoder::class);

        /** @var Collection&MockInterface $tokenResources */
        $tokenResources = Mockery::mock(Collection::class);

        /** @var EloquentUser&MockInterface $user */
        $user = Mockery::mock(EloquentUser::class);

        $service = new AssignmentJwtTokenService($this->getConfigFixture(), $tokenEncoder);

        $this->expectExceptionObject(
            new AssignmentRuntimeException(
                'You are not allowed to create tokens valid for more than 1440 minutes (1 day)!',
            ),
        );

        $service->createToken($tokenResources, $user, ttlExpirationInMinutes: 24 * 60 + 1);
    }

    public function testItThrowsAnExceptionWhenExpirationTtlIsToLongWhenCallingCreateTokenForCases(): void
    {
        /** @var TokenEncoder&MockInterface $tokenEncoder */
        $tokenEncoder = Mockery::mock(TokenEncoder::class);

        /** @var EloquentUser&MockInterface $user */
        $user = Mockery::mock(EloquentUser::class);

        $service = new AssignmentJwtTokenService($this->getConfigFixture(), $tokenEncoder);

        $this->expectExceptionObject(
            new AssignmentRuntimeException(
                'You are not allowed to create tokens valid for more than 1440 minutes (1 day)!',
            ),
        );

        $service->createTokenForCases(uuids: [], user: $user, ttlExpirationInMinutes: 24 * 60 + 1);
    }

    public function getConfigFixture(): Config
    {
        return new Config([
            'assignment' => [
                'jwt' => [
                    'issuer' => 'my_issuer',
                    'audience' => 'my_audience',
                ],
                'stateless' => [
                    'cases' => [
                        'max_uuids' => 100,
                    ],
                ],
            ],
        ]);
    }
}
