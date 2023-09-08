<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Assignment;

use App\Models\Eloquent\EloquentUser;
use App\Services\Assignment\AssignmentTokenable;
use App\Services\Assignment\Enum\AssignmentModelEnum;
use App\Services\Assignment\HasAssignmentToken;
use App\Services\Assignment\Token;
use App\Services\Assignment\TokenResource;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

#[Group('assignment')]
class AssignmentTokenableTest extends FeatureTestCase
{
    private HasAssignmentToken $hasAssignmentToken;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hasAssignmentToken = new class () implements HasAssignmentToken
        {
            use AssignmentTokenable;
        };
    }

    public function testAllowedByToken(): void
    {
        $user = $this->createUserWithOrganisation();
        $token = $this->getValidToken(['uuid_one'], $user);

        $this->hasAssignmentToken->setToken($token);

        $this->assertTrue(
            $this->hasAssignmentToken->allowedByToken(AssignmentModelEnum::Case_, ['uuid_one']),
            'It should have allowed uuid_one',
        );
    }

    public function testAllowedByTokenWithoutToken(): void
    {
        $this->assertFalse(
            $this->hasAssignmentToken->allowedByToken(AssignmentModelEnum::Case_, ['uuid_one']),
            'It should always return false when the request has no token',
        );
    }

    public function testAllowedCasesByToken(): void
    {
        $user = $this->createUserWithOrganisation();
        $token = $this->getValidToken(['uuid_one'], $user);

        $this->hasAssignmentToken->setToken($token);

        $this->assertTrue($this->hasAssignmentToken->allowedCasesByToken(['uuid_one']), 'It should have allowed uuid_one');
    }

    private function getValidToken(
        array $caseUuids,
        EloquentUser $user,
        int $ttlExpirationInMinutes = 10,
    ): Token {
        $tokenResources = Collection::make([new TokenResource(mod: AssignmentModelEnum::Case_, ids: $caseUuids)])
            ->add(new TokenResource(mod: AssignmentModelEnum::Case_, ids: ['added_uuid_one', 'added_uuid_two']));

        return new Token(
            iss: 'issuer',
            aud: 'audience',
            sub: $user->uuid,
            exp: CarbonImmutable::now()->addMinutes($ttlExpirationInMinutes)->timestamp,
            iat: CarbonImmutable::now()->timestamp,
            jti: null,
            res: $tokenResources,
        );
    }
}
