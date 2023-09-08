<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Assignment\TokenFetcher;

use App\Services\Assignment\Exception\AssignmentInvalidValueException;
use App\Services\Assignment\Exception\AssignmentRuntimeException;
use App\Services\Assignment\TokenFetcher\RequestHeaderTokenFetcher;
use App\Services\Assignment\TokenFetcher\TokenFetcher;
use Illuminate\Config\Repository as Config;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;

#[Group('assignment')]
final class RequestHeaderTokenFetcherTest extends UnitTestCase
{
    public function testItCanBeInitialized(): void
    {
        $request = $this->createMock(Request::class);
        $config = $this->getConfig();

        $fetcher = new RequestHeaderTokenFetcher($request, $config);

        $this->assertInstanceOf(TokenFetcher::class, $fetcher);
        $this->assertInstanceOf(RequestHeaderTokenFetcher::class, $fetcher);
    }

    public function testItThrowsAnAssignmentInvalidValueExceptionWhenTypeOfHeaderNameIsNotString(): void
    {
        $request = $this->createMock(Request::class);
        $config = new Config();

        $this->expectExceptionObject(
            new AssignmentInvalidValueException('Invalid type for value "headerName" given. Expected a "string", but got a "NULL"'),
        );

        new RequestHeaderTokenFetcher($request, $config);
    }

    public function testHasTokenOnRequestWithAStringToken(): void
    {
        $config = $this->getConfig();

        $request = $this->createMock(Request::class);
        $request->expects($this->once())->method('hasHeader')
            ->with('Assignment-Token')
            ->willReturn(true);
        $request->expects($this->once())->method('header')
            ->with('Assignment-Token')
            ->willReturn('my_token');

        $fetcher = new RequestHeaderTokenFetcher($request, $config);

        $this->assertTrue($fetcher->hasToken());
    }

    public function testHasTokenOnRequestWithoutAToken(): void
    {
        $config = $this->getConfig();

        $request = $this->createMock(Request::class);
        $request->expects($this->once())->method('hasHeader')
            ->with('Assignment-Token')
            ->willReturn(false);

        $fetcher = new RequestHeaderTokenFetcher($request, $config);

        $this->assertFalse($fetcher->hasToken());
    }

    public function testHasTokenOnRequestWithAnArrayToken(): void
    {
        $token = $this->faker->words();

        $config = $this->getConfig();

        $request = $this->createMock(Request::class);
        $request->expects($this->once())->method('hasHeader')
            ->with('Assignment-Token')
            ->willReturn(true);
        $request->expects($this->once())->method('header')
            ->with('Assignment-Token')
            ->willReturn($token);

        $fetcher = new RequestHeaderTokenFetcher($request, $config);

        $this->assertFalse($fetcher->hasToken());
    }

    public function testGetTokenOnRequestWithAStringToken(): void
    {
        $token = $this->faker->word();

        $request = $this->createMock(Request::class);
        $request->expects($this->once())->method('hasHeader')
            ->with('Assignment-Token')
            ->willReturn(true);
        $request->expects($this->exactly(2))->method('header')
            ->with('Assignment-Token')
            ->willReturn($token);

        $config = $this->getConfig();

        $fetcher = new RequestHeaderTokenFetcher($request, $config);

        $this->assertSame($token, $fetcher->getToken());
    }

    public function testItThrowsAnExceptionWhenTryingToGetATokenOnRequestWithAnEmptyToken(): void
    {
        $request = $this->createMock(Request::class);
        $request->expects($this->once())->method('hasHeader')
            ->with('Assignment-Token')
            ->willReturn(true);
        $request->expects($this->exactly(2))->method('header')
            ->with('Assignment-Token')
            ->willReturn(null);

        $config = $this->getConfig();

        $this->expectExceptionObject(new AssignmentRuntimeException('Failed fetching token header "Assignment-Token"'));

        (new RequestHeaderTokenFetcher($request, $config))->getToken();
    }

    public function testItThrowsAnExceptionWhenTryingToGetATokenOnRequestWithAnArrayToken(): void
    {
        $request = $this->createMock(Request::class);
        $request->expects($this->once())->method('hasHeader')
            ->with('Assignment-Token')
            ->willReturn(true);
        $request->expects($this->exactly(2))->method('header')
            ->with('Assignment-Token')
            ->willReturn(['one', 'two']);

        $config = $this->getConfig();

        $this->expectExceptionObject(new AssignmentRuntimeException('Failed fetching token header "Assignment-Token"'));

        (new RequestHeaderTokenFetcher($request, $config))->getToken();
    }

    private function getConfig(): Config
    {
        return new Config([
            'assignment' => [
                'token_fetcher' => [
                    'request_header' => [
                        'header_name' => 'Assignment-Token',
                    ],
                ],
            ],
        ]);
    }
}
