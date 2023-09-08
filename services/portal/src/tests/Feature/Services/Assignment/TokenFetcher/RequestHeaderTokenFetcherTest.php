<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Assignment\TokenFetcher;

use App\Services\Assignment\Exception\AssignmentRuntimeException;
use App\Services\Assignment\TokenFetcher\RequestHeaderTokenFetcher;
use App\Services\Assignment\TokenFetcher\TokenFetcher;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function sprintf;
use function strtolower;

#[Group('assignment')]
final class RequestHeaderTokenFetcherTest extends FeatureTestCase
{
    private Config $config;
    private string $assignmentHeader;

    protected function setUp(): void
    {
        parent::setUp();

        $this->config = $this->app->make(Config::class);
        $this->assignmentHeader = $this->config->get('assignment.token_fetcher.request_header.header_name');
    }

    public function testItCanBeInitialized(): void
    {
        $fetcher = $this->app->make(RequestHeaderTokenFetcher::class);

        $this->assertInstanceOf(RequestHeaderTokenFetcher::class, $fetcher);
        $this->assertInstanceOf(TokenFetcher::class, $fetcher);
    }

    public function testHasTokenWithRequestWithToken(): void
    {
        /** @var Request $request */
        $request = $this->app->make(Request::class);
        $request->headers->set($this->assignmentHeader, $this->faker->word());

        /** @var RequestHeaderTokenFetcher $fetcher */
        $fetcher = $this->app->make(RequestHeaderTokenFetcher::class);

        $this->assertTrue($fetcher->hasToken(), 'Fetcher should have found a token!');
    }

    public function testHashTokenWithRequestWithoutToken(): void
    {
        /** @var RequestHeaderTokenFetcher $fetcher */
        $fetcher = $this->app->make(RequestHeaderTokenFetcher::class);

        $this->assertFalse($fetcher->hasToken(), 'Fetcher should not have found a token!');
    }

    public function testGetTokenWithRequestWithToken(): void
    {
        /** @var Request $request */
        $request = $this->app->make(Request::class);
        $request->headers->set($this->assignmentHeader, $token = $this->faker->word());

        /** @var RequestHeaderTokenFetcher $fetcher */
        $fetcher = $this->app->make(RequestHeaderTokenFetcher::class);

        $this->assertSame($token, $fetcher->getToken(), 'Fetcher should have found a token!');
    }

    public function testItShouldThrowAnExceptionWhenCallingGetTokenWithRequestWithoutToken(): void
    {
        /** @var RequestHeaderTokenFetcher $fetcher */
        $fetcher = $this->app->make(RequestHeaderTokenFetcher::class);

        $this->expectExceptionObject(
            new AssignmentRuntimeException(sprintf('Failed fetching token header "%s".', $this->assignmentHeader)),
        );

        $fetcher->getToken();
    }

    public function testGetTokenWithRequestWithTokenIsCaseInsensitive(): void
    {
        $upperCasedHeaderName = 'UPPER-CASE-HEADER-NAME';
        $lowerCasedHeaderName = strtolower($upperCasedHeaderName);

        $this->config->set('assignment.token_fetcher.request_header.header_name', $upperCasedHeaderName);

        /** @var Request $request */
        $request = $this->app->make(Request::class);
        $request->headers->set($lowerCasedHeaderName, $token = $this->faker->word());

        /** @var RequestHeaderTokenFetcher $fetcher */
        $fetcher = $this->app->make(RequestHeaderTokenFetcher::class);

        $this->assertSame($token, $fetcher->getToken(), 'Fetcher should have found a token!');
    }
}
