<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Assignment;

use App\Services\Assignment\AssignmentTokenable;
use App\Services\Assignment\Exception\AssignmentRuntimeException;
use App\Services\Assignment\HasAssignmentToken;
use App\Services\Assignment\Token;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;

#[Group('assignment')]
class AssignmentTokenableTest extends UnitTestCase
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

    public function testItIsInstantianable(): void
    {
        $this->assertInstanceof(HasAssignmentToken::class, $this->hasAssignmentToken);
    }

    public function testContractMethods(): void
    {
        /** @var Token&MockInterface $token */
        $token = Mockery::mock(Token::class);

        $this->assertFalse($this->hasAssignmentToken->hasToken(), 'The object should not have a token!');

        $this->hasAssignmentToken->setToken($token);

        $this->assertTrue($this->hasAssignmentToken->hasToken(), 'The object should have a token!');
        $this->assertSame($token, $this->hasAssignmentToken->getToken());
    }

    public function testItThrowsAnAssignmentRuntimeExceptionIfNoTokenAvailable(): void
    {
        $this->expectExceptionObject(new AssignmentRuntimeException('This user does not have a token!'));

        $this->hasAssignmentToken->getToken();
    }
}
