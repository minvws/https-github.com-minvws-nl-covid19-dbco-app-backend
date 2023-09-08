<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Assignment\TokenProcessor;

use App\Services\Assignment\TokenProcessor\JwtTokenProcessor;
use App\Services\Assignment\TokenProcessor\TokenProcessor;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;

#[Group('assignment')]
final class JwtTokenProcessorTest extends UnitTestCase
{
    public function testItCanBeInitialized(): void
    {
        /** @var validationFactory&MockInterface $validationFactory */
        $validationFactory = Mockery::mock(ValidationFactory::class);

        $statelessTokenProcessor = new JwtTokenProcessor($validationFactory);

        $this->assertInstanceOf(JwtTokenProcessor::class, $statelessTokenProcessor);
        $this->assertInstanceOf(TokenProcessor::class, $statelessTokenProcessor);
    }
}
