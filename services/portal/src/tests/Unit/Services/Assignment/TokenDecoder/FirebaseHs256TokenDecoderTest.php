<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Assignment\TokenDecoder;

use App\Services\Assignment\Exception\AssignmentBeforeValidException;
use App\Services\Assignment\Exception\AssignmentDomainException;
use App\Services\Assignment\Exception\AssignmentExpiredException;
use App\Services\Assignment\Exception\AssignmentInvalidValueException;
use App\Services\Assignment\Exception\AssignmentSignatureInvalidException;
use App\Services\Assignment\Exception\AssignmentUnexpectedValueException;
use App\Services\Assignment\TokenDecoder\FirebaseHs256TokenDecoder;
use App\Services\Assignment\TokenDecoder\TokenDecoder;
use DomainException;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Illuminate\Config\Repository as Config;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;
use stdClass;
use Tests\Unit\UnitTestCase;
use UnexpectedValueException;

#[Group('assignment')]
final class FirebaseHs256TokenDecoderTest extends UnitTestCase
{
    public function testItCanBeInitialized(): void
    {
        $decoder = new FirebaseHs256TokenDecoder($this->getConfig());

        $this->assertInstanceOf(FirebaseHs256TokenDecoder::class, $decoder);
        $this->assertInstanceOf(TokenDecoder::class, $decoder);
    }

    public function testItThrowsAnAssignmentInvalidValueExceptionWhenTypeOfHeaderNameIsNotString(): void
    {
        $config = new Config();

        $this->expectExceptionObject(
            new AssignmentInvalidValueException('Invalid type for value "jwtKey" given. Expected a "string", but got a "NULL"'),
        );

        new FirebaseHs256TokenDecoder($config);
    }

    public function testItCanDecode(): void
    {
        $doDecodeResult = new stdClass();

        $decoder = $this->getPartialdecoderMock();
        $decoder->expects('doDecode')->andReturn($doDecodeResult);

        $this->assertSame($doDecodeResult, $decoder->decode($this->faker->word()));
    }

    public function testItWrapsSignatureInvalidExceptions(): void
    {
        $decoder = $this->getPartialdecoderMock();
        $decoder->expects('doDecode')->andThrow($originalEx = new SignatureInvalidException());

        $this->expectException(AssignmentSignatureInvalidException::class);

        try {
            $decoder->decode('');
        } catch (AssignmentSignatureInvalidException $e) {
            $this->assertSame($originalEx, $e->getPrevious(), 'New exception did not contain the original exception.');

            throw $e;
        }
    }

    public function testItWrapsBeforeValidationExceptions(): void
    {
        $decoder = $this->getPartialdecoderMock();
        $decoder->expects('doDecode')->andThrow($originalEx = new BeforeValidException());

        $this->expectException(AssignmentBeforeValidException::class);

        try {
            $decoder->decode('');
        } catch (AssignmentBeforeValidException $e) {
            $this->assertSame($originalEx, $e->getPrevious(), 'New exception did not contain the original exception.');

            throw $e;
        }
    }

    public function testItWrapsExpiredExceptions(): void
    {
        $decoder = $this->getPartialdecoderMock();
        $decoder->expects('doDecode')->andThrow($originalEx = new ExpiredException());

        $this->expectException(AssignmentExpiredException::class);

        try {
            $decoder->decode('');
        } catch (AssignmentExpiredException $e) {
            $this->assertSame($originalEx, $e->getPrevious(), 'New exception did not contain the original exception.');

            throw $e;
        }
    }

    public function testItWrapsUnexpectedValueExceptions(): void
    {
        $decoder = $this->getPartialdecoderMock();
        $decoder->expects('doDecode')->andThrow($originalEx = new UnexpectedValueException());

        $this->expectException(AssignmentUnexpectedValueException::class);

        try {
            $decoder->decode('');
        } catch (AssignmentUnexpectedValueException $e) {
            $this->assertSame($originalEx, $e->getPrevious(), 'New exception did not contain the original exception.');

            throw $e;
        }
    }

    public function testItWrapsDomainExceptions(): void
    {
        $decoder = $this->getPartialdecoderMock();
        $decoder->expects('doDecode')->andThrow($originalEx = new DomainException());

        $this->expectException(AssignmentDomainException::class);

        try {
            $decoder->decode('');
        } catch (AssignmentDomainException $e) {
            $this->assertSame($originalEx, $e->getPrevious(), 'New exception did not contain the original exception.');

            throw $e;
        }
    }

    private function getPartialDecoderMock(): FirebaseHs256TokenDecoder&MockInterface
    {
        /** @var FirebaseHs256TokenDecoder&MockInterface $decoder */
        $decoder = Mockery::mock(FirebaseHs256TokenDecoder::class . '[doDecode]', [$this->getConfig()]);
        $decoder->shouldAllowMockingProtectedMethods();

        return $decoder;
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
