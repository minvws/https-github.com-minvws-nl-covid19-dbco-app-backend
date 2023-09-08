<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Assignment\TokenEncoder;

use App\Services\Assignment\Exception\AssignmentDomainException;
use App\Services\Assignment\Exception\AssignmentInvalidArgumentException;
use App\Services\Assignment\Token;
use App\Services\Assignment\TokenEncoder\FirebaseHs256TokenEncoder;
use App\Services\Assignment\TokenEncoder\TokenEncoder;
use DomainException;
use Illuminate\Config\Repository as Config;
use InvalidArgumentException;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;

#[Group('assignment')]
final class FirebaseHs256TokenEncoderTest extends UnitTestCase
{
    public function testItCanBeInitialized(): void
    {
        $decoder = new FirebaseHs256TokenEncoder($this->getConfig());

        $this->assertInstanceOf(FirebaseHs256TokenEncoder::class, $decoder);
        $this->assertInstanceOf(TokenEncoder::class, $decoder);
    }

    public function testItCanEncode(): void
    {
        $doEncodeResult = $this->faker->uuid();

        /** @var FirebaseHs256TokenEncoder&MockInterface $encoder */
        $encoder = Mockery::mock(FirebaseHs256TokenEncoder::class . '[doEncode]', [$this->getConfig()]);
        $encoder->shouldAllowMockingProtectedMethods();
        $encoder->expects('doEncode')->andReturn($doEncodeResult);

        /** @var Token&MockInterface $payload */
        $payload = Mockery::mock(Token::class);
        $payload->expects('toArray')->andReturn([]);

        $this->assertSame($doEncodeResult, $encoder->encode($payload));
    }

    public function testItWrapsDomainExceptions(): void
    {
        /** @var FirebaseHs256TokenEncoder&MockInterface $encoder */
        $encoder = Mockery::mock(FirebaseHs256TokenEncoder::class . '[doEncode]', [$this->getConfig()]);
        $encoder->shouldAllowMockingProtectedMethods();
        $encoder->expects('doEncode')->andThrow($originalEx = new DomainException());

        /** @var Token&MockInterface $payload */
        $payload = Mockery::mock(Token::class);
        $payload->expects('toArray')->andReturn([]);

        $this->expectException(AssignmentDomainException::class);

        try {
            $encoder->encode($payload);
        } catch (AssignmentDomainException $e) {
            $this->assertSame($originalEx, $e->getPrevious(), 'New exception did not contain the original exception.');

            throw $e;
        }
    }

    public function testItWrapsInvalidArgumentExceptions(): void
    {
        /** @var FirebaseHs256TokenEncoder&MockInterface $encoder */
        $encoder = Mockery::mock(FirebaseHs256TokenEncoder::class . '[doEncode]', [$this->getConfig()]);
        $encoder->shouldAllowMockingProtectedMethods();
        $encoder->expects('doEncode')->andThrow($originalEx = new InvalidArgumentException());

        /** @var Token&MockInterface $payload */
        $payload = Mockery::mock(Token::class);
        $payload->expects('toArray')->andReturn([]);

        $this->expectException(AssignmentInvalidArgumentException::class);

        try {
            $encoder->encode($payload);
        } catch (AssignmentInvalidArgumentException $e) {
            $this->assertSame($originalEx, $e->getPrevious(), 'New exception did not contain the original exception.');

            throw $e;
        }
    }

    private function getConfig(): Config
    {
        return new Config([
            'assignment' => [
                'jwt' => [
                    'secret' => 'MY_JWT_SECRET',
                ],
            ],
        ]);
    }
}
