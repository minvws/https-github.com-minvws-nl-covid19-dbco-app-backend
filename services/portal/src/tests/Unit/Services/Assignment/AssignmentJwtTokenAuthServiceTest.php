<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Assignment;

use App\Services\Assignment\AssignmentJwtTokenAuthService;
use App\Services\Assignment\AssignmentTokenAuthService;
use App\Services\Assignment\TokenDecoder\TokenDecoder;
use App\Services\Assignment\TokenFetcher\TokenFetcher;
use App\Services\Assignment\TokenProcessor\JwtTokenProcessor;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;
use Psr\Log\LoggerInterface;
use Tests\Unit\UnitTestCase;

#[Group('assignment')]
final class AssignmentJwtTokenAuthServiceTest extends UnitTestCase
{
    public function testItCanBeInitialized(): void
    {
        /** @var TokenFetcher&MockInterface $tokenFetcher */
        $tokenFetcher = Mockery::mock(TokenFetcher::class);

        /** @var TokenDecoder&MockInterface $tokenDecoder */
        $tokenDecoder = Mockery::mock(TokenDecoder::class);

        /** @var JwtTokenProcessor&MockInterface $tokenProcessor */
        $tokenProcessor = Mockery::mock(JwtTokenProcessor::class);

        /** @var LoggerInterface&MockInterface $logger */
        $logger = Mockery::mock(LoggerInterface::class);

        $service = new AssignmentJwtTokenAuthService($tokenFetcher, $tokenDecoder, $tokenProcessor, $logger);

        $this->assertInstanceOf(AssignmentJwtTokenAuthService::class, $service);
        $this->assertInstanceOf(AssignmentTokenAuthService::class, $service);
    }
}
