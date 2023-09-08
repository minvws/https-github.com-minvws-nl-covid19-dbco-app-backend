<?php

declare(strict_types=1);

namespace Tests\Feature\Http\CircuitBreaker;

use Ackintosh\Ganesha;
use Ackintosh\Ganesha\Builder;
use Ackintosh\Ganesha\Storage\Adapter\Redis;
use Ackintosh\Ganesha\Storage\AdapterInterface;
use App\Http\CircuitBreaker\CircuitBreaker;
use App\Http\CircuitBreaker\CircuitBreakerMiddleware;
use App\Http\CircuitBreaker\Exceptions\NotAvailableException;
use App\Http\CircuitBreaker\GaneshaCircuitBreaker;
use App\Models\Metric\CircuitBreaker\Availability;
use App\Repositories\Metric\MetricRepository;
use App\Services\CircuitBreakerService;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Redis\RedisManager;
use Mockery;
use Mockery\MockInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use RedisException;
use Tests\Feature\FeatureTestCase;

use function sprintf;

final class CircuitBreakerMiddlewareTest extends FeatureTestCase
{
    private AdapterInterface $adapter;

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function setUp(): void
    {
        parent::setUp();

        $redisManager = $this->app->get(RedisManager::class);
        $connection = $redisManager->connection('ganesha');

        $this->adapter = new Redis($connection->client());
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws RedisException
     */
    protected function tearDown(): void
    {
        $redisManager = $this->app->get(RedisManager::class);
        $connection = $redisManager->connection('ganesha');

        $client = $connection->client();
        $client->flushall();

        parent::tearDown();
    }

    public function testRegisterSuccess(): void
    {
        $service = $this->faker->word();
        $url = $this->faker->url;

        $mockHandler = new MockHandler([new Response()]);
        $circuitBreaker = $this->buildGaneshaCircuitBreaker();

        $client = $this->createClient($mockHandler, $circuitBreaker, $service);
        $response = $client->post($url);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(Ganesha::STATUS_CALMED_DOWN, $this->adapter->loadStatus($service));
    }

    /**
     * @throws GuzzleException
     */
    public function testCircuitBreaker(): void
    {
        $service = $this->faker->word();
        $url = $this->faker->url;
        $circuitBreaker = $this->buildGaneshaCircuitBreaker(minimumRequests: 3);

        $this->mock(MetricRepository::class, static function (MockInterface $mock) use ($service): void {
            $method = 'measureGauge';
            $available = Mockery::on(static function (Availability $availability) use ($service): bool {
                return $availability->getValue() === 0.0
                    && $availability->getName() === sprintf('%s_circuit_breaker_gauge', $service);
            });
            $notAvailable = Mockery::on(static function (Availability $availability) use ($service): bool {
                return $availability->getValue() === 1.0
                    && $availability->getName() === sprintf('%s_circuit_breaker_gauge', $service);
            });
            $mock->expects($method)->ordered()->with($available);
            $mock->expects($method)->ordered()->with($available);
            $mock->expects($method)->ordered()->with($available);
            $mock->expects($method)->ordered()->with($notAvailable);
        });

        $mockHandler = new MockHandler([
            new RequestException($this->faker->sentence(), new Request('POST', $url)),
            new RequestException($this->faker->sentence(), new Request('POST', $url)),
            new RequestException($this->faker->sentence(), new Request('POST', $url)),
        ]);

        $client = $this->createClient($mockHandler, $circuitBreaker, $service);
        $this->doRequestAndCatchRequestException($client, $url);
        $this->doRequestAndCatchRequestException($client, $url);
        $this->doRequestAndCatchRequestException($client, $url);

        $this->expectException(NotAvailableException::class);
        $this->doRequest($client, $url);

        $this->assertSame(0, $this->adapter->loadStatus($service));
    }

    private function createClient(MockHandler $mockHandler, CircuitBreaker $circuitBreaker, string $service): Client
    {
        $circuitBreakerService = $this->app->make(
            CircuitBreakerService::class,
            ['circuitBreaker' => $circuitBreaker],
        );

        $circuitBreakerMiddleware = $this->app->make(
            CircuitBreakerMiddleware::class,
            ['circuitBreakerService' => $circuitBreakerService],
        );

        $handlerStack = HandlerStack::create($mockHandler);
        $handlerStack->push($circuitBreakerMiddleware);

        return new Client(['handler' => $handlerStack, 'service_name' => $service]);
    }

    /**
     * @throws GuzzleException
     */
    private function doRequestAndCatchRequestException(Client $client, string $uri): void
    {
        $exceptionThrown = false;

        try {
            $this->doRequest($client, $uri);
        } catch (RequestException) {
            $exceptionThrown = true;
        }

        $this->assertTrue(
            $exceptionThrown,
            sprintf('Expected a "%s" to be thrown', RequestException::class),
        );
    }

    /**
     * @throws GuzzleException
     */
    private function doRequest(Client $client, string $uri): void
    {
        $client->post($uri);
    }

    private function buildGaneshaCircuitBreaker(int $minimumRequests = 1): GaneshaCircuitBreaker
    {
        $ganesha = Builder::withRateStrategy()
            ->minimumRequests($minimumRequests)
            ->intervalToHalfOpen(5)
            ->timeWindow(60)
            ->failureRateThreshold(50)
            ->adapter($this->adapter)
            ->build();

        return new GaneshaCircuitBreaker($ganesha);
    }
}
