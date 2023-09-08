<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Client;

use Ackintosh\Ganesha;
use App\Helpers\Config;
use App\Http\CircuitBreaker\Exceptions\NotAvailableException;
use App\Http\Client\Guzzle\MittensClient;
use App\Http\Client\Guzzle\MittensClientException;
use App\Http\Client\Guzzle\MittensClientInterface;
use App\Http\Requests\Mittens\MittensRequest;
use App\Models\Metric\CircuitBreaker\Availability;
use App\Models\Metric\Mittens\MittensRequest as RequestMetric;
use App\Repositories\Metric\MetricRepository;
use App\Services\MetricService;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use JsonException;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Log\LoggerInterface;
use Tests\Feature\FeatureTestCase;

use function config;
use function json_encode;
use function sprintf;

use const JSON_THROW_ON_ERROR;

final class MittensClientTest extends FeatureTestCase
{
    private const PSEUDO_BSN_FIXTURE = '9fc3e93e-e24d-4064-5717-7b4b41cb8993';
    private const CENCORED_BSN_FIXTURE = '******123';
    private const LETTERS_FIXTURE = 'EJ';

    /**
     * @throws MittensClientException
     * @throws JsonException
     */
    public function testSuccessfulResponse(): void
    {
        $metricRepository = $this->createMock(MetricRepository::class);
        $metricRepository->expects($this->once())
            ->method('measureCounter')
            ->with($this->callback(static function (RequestMetric $metric): bool {
                return $metric->getLabels() === [
                    'uri' => '/service/via_digid/',
                    'status' => '202',
                ];
            }));

        $container = [];
        $history = Middleware::history($container);

        $responseGuid = self::PSEUDO_BSN_FIXTURE;
        $responseCensoredBsn = self::CENCORED_BSN_FIXTURE;
        $responseLetters = self::LETTERS_FIXTURE;
        $pseudoBsnToken = $this->faker->uuid();

        $mockHandler = new MockHandler([
            new Response(202, ['Content-Type' => 'application/json'], json_encode([
                'data' => [
                    (object) [
                        'guid' => $responseGuid,
                        'censored_bsn' => $responseCensoredBsn,
                        'letters' => $responseLetters,
                        'token' => $pseudoBsnToken,
                    ],
                ],
            ], JSON_THROW_ON_ERROR)),
        ]);
        $handlerStack = HandlerStack::create($mockHandler);
        $handlerStack->push($history);

        $client = new Client(['handler' => $handlerStack]);
        $mittensClient = $this->getMittensClient($client, $metricRepository);

        $actual = $mittensClient->post(new MittensRequest('/service/via_digid/', []));

        $expected = (object) [
            'data' => [
                (object) [
                    'guid' => $responseGuid,
                    'censored_bsn' => $responseCensoredBsn,
                    'letters' => $responseLetters,
                    'token' => $pseudoBsnToken,
                ],
            ],
        ];

        $this->assertEquals($expected, $actual);
    }

    /**
     * @throws JsonException
     */
    public function testErrorDetailsInResponse(): void
    {
        $container = [];
        $history = Middleware::history($container);

        $statusCode = 400;
        $firstErrorMessage = 'No answer found for this query.';

        $mockHandler = new MockHandler([
            new Response(
                $statusCode,
                ['Content-Type' => 'application/json'],
                json_encode([
                    'errors' => [
                        $firstErrorMessage,
                        'Error occurred when retrieving data. Check errors for more details.',
                        'Could not find a result.',
                    ],
                ], JSON_THROW_ON_ERROR),
            ),
        ]);
        $handlerStack = HandlerStack::create($mockHandler);
        $handlerStack->push($history);

        $client = new Client(['handler' => $handlerStack]);
        $mittensClient = $this->getMittensClient($client);

        $this->expectException(MittensClientException::class);
        $this->expectExceptionMessage($firstErrorMessage);
        $this->expectExceptionCode($statusCode);

        $mittensClient->post(new MittensRequest('/service/via_pii/', []));
    }

    /**
     * @throws MittensClientException
     * @throws JsonException
     */
    public function testNoDataInResponse(): void
    {
        $container = [];
        $history = Middleware::history($container);

        $responseData = ['no-data-in-response' => []];

        $mockHandler = new MockHandler([
            new Response(
                202,
                ['Content-Type' => 'application/json'],
                json_encode($responseData),
            ),
        ]);
        $handlerStack = HandlerStack::create($mockHandler);
        $handlerStack->push($history);

        $client = new Client(['handler' => $handlerStack]);
        $mittensClient = $this->getMittensClient($client);

        $actual = $mittensClient->post(
            new MittensRequest('/service/via_pii/', []),
        );

        $expected = (object) $responseData;
        $this->assertEquals($expected, $actual);
    }

    /**
     * @throws JsonException
     */
    public function testConnectionErrorResponse(): void
    {
        $container = [];
        $history = Middleware::history($container);

        $mockHandler = new MockHandler([
            new RequestException(
                'Error Communicating with Server',
                (new MittensRequest('/service/via_pii', []))->toGuzzleRequest(),
            ),
        ]);
        $handlerStack = HandlerStack::create($mockHandler);
        $handlerStack->push($history);

        $client = new Client(['handler' => $handlerStack]);
        $mittensClient = $this->getMittensClient($client);

        $this->expectException(MittensClientException::class);
        $this->expectExceptionMessage('Error Communicating with Server');
        $this->expectExceptionCode(0);

        $mittensClient->post(new MittensRequest('/service/via_pii/', []));
    }

    public function testUnauthorisedResponse(): void
    {
        $container = [];
        $history = Middleware::history($container);

        $statusCode = 401;
        $errorMessage = 'Unauthorized';

        $mockHandler = new MockHandler([
            new Response(
                $statusCode,
                ['Content-Type' => 'application/json'],
                json_encode(['errors' => [$errorMessage]]),
            ),
        ]);

        $handlerStack = HandlerStack::create($mockHandler);
        $handlerStack->push($history);

        $client = new Client(['handler' => $handlerStack]);
        $mittensClient = $this->getMittensClient($client);

        $this->expectException(MittensClientException::class);
        $this->expectExceptionMessage($errorMessage);
        $this->expectExceptionCode($statusCode);

        $mittensClient->post(new MittensRequest('/service/via_pii/', []));
    }

    /**
     * @throws JsonException
     */
    public function testServerErrorResponse(): void
    {
        config()->set('services.mittens.max_retry_count', 0);

        $container = [];
        $history = Middleware::history($container);

        $statusCode = 500;
        $errorMessage = 'Internal server error';
        $uri = '/service/via_pii';

        $mockHandler = new MockHandler([
            new ServerException(
                $errorMessage,
                (new MittensRequest($uri, []))->toGuzzleRequest(),
                new Response($statusCode, [], json_encode($errorMessage)),
            ),
        ]);

        $handlerStack = HandlerStack::create($mockHandler);
        $handlerStack->push($history);

        $client = new Client(['handler' => $handlerStack]);
        $mittensClient = $this->getMittensClient($client);

        $this->expectException(MittensClientException::class);
        $this->expectExceptionMessage('service unavailable');
        $this->expectExceptionCode($statusCode);

        $mittensClient->post(new MittensRequest('/service/via_pii/', []));
    }

    #[DataProvider('dpRetryMechanism')]
    public function testRetryMechanism(int $maxRetryCount, int $expectedRequestCount): void
    {
        $container = [];
        $history = Middleware::history($container);

        $statusCode = 400;
        $jsonBody = json_encode(['errors' => ['service unavailable']]);
        $mockHandler = new MockHandler([
            new Response($statusCode, ['Content-Type' => 'application/json'], $jsonBody),
            new Response($statusCode, ['Content-Type' => 'application/json'], $jsonBody),
            new Response($statusCode, ['Content-Type' => 'application/json'], $jsonBody),
            new Response($statusCode, ['Content-Type' => 'application/json'], $jsonBody),
        ]);

        $handlerStack = HandlerStack::create($mockHandler);
        $handlerStack->push($history);

        $mittensClient = $this->getMittensClient(
            new Client(['handler' => $handlerStack]),
            $this->createMock(MetricRepository::class),
        );

        $this->expectException(MittensClientException::class);
        $this->expectExceptionMessage('service unavailable');
        $this->expectExceptionCode($statusCode);

        $mittensClient->post(new MittensRequest('/service/via_pii/', []));

        // assert the amount of requests made
        $this->assertCount($expectedRequestCount, $container);
    }

    public static function dpRetryMechanism(): array
    {
        return [
            'no retries, max 1 request' => [0, 1],
            'retry once, max 2 requests' => [1, 2],
            '2 retries, max 3 requests' => [2, 3],
        ];
    }

    public function testCircuitBreaker(): void
    {
        config()->set('services.mittens.rate_strategy.time_window', 100);
        config()->set('services.mittens.rate_strategy.failure_rate_threshold', 1);
        config()->set('services.mittens.rate_strategy.minimum_requests', 1);
        config()->set('services.mittens.rate_strategy.interval_to_half_open', 5000);

        $service = Config::string('services.mittens.client_options.service_name');

        $this->mock(MetricRepository::class, static function (MockInterface $mock) use ($service): void {
            $mock->expects('measureGauge')
                ->with(Mockery::on(static function (Availability $availability) use ($service): bool {
                    $name = sprintf('%s_circuit_breaker_gauge', $service);

                    $hasValidName = $availability->getName() === $name;
                    $hasValidValue = $availability->getValue() === 1.0;

                    return $hasValidName && $hasValidValue;
                }));

            $mock->allows('measureCounter');
        });

        $ganesha = $this->app->get('mittens.ganesha');
        $this->assertInstanceOf(Ganesha::class, $ganesha);

        $ganesha->failure('mittens');

        $mittensClient = $this->app->get(MittensClientInterface::class);

        $this->expectException(MittensClientException::class);
        $this->expectExceptionMessage(sprintf('Circuit breaker not available for service: "%s"', $service));

        $mittensClient->post(new MittensRequest('/foo', []));
    }

    public function testItMeasuresAConnectionError(): void
    {
        $this->mock(MetricService::class, static function (MockInterface $mock): void {
            $mock->expects('measure')
                ->withArgs(
                    static fn (RequestMetric $metric): bool =>
                        $metric->getLabels()['status'] === 'connection_error',
                );
        });

        $mittensRequest = new MittensRequest('/foo');

        $client = Mockery::mock(ClientInterface::class);
        $client->expects('send')
            ->andThrow(
                new ConnectException(
                    'Connection timed out',
                    $mittensRequest->toGuzzleRequest(),
                ),
            );

        $mittensClient = $this->app->make(
            MittensClientInterface::class,
            ['client' => $client],
        );

        $this->expectException(MittensClientException::class);
        $mittensClient->post($mittensRequest);
    }

    public function testItMeasuresAnInterceptedRequestWhenCircuitBreakerIsNotAvailable(): void
    {
        $this->mock(MetricService::class, static function (MockInterface $mock): void {
            $mock->expects('measure')
                ->withArgs(
                    static fn (RequestMetric $metric): bool
                        => $metric->getLabels()['status'] === 'circuit_breaker_intercepted',
                );
        });

        $client = Mockery::mock(ClientInterface::class);
        $client->expects('send')
            ->andThrow(new NotAvailableException('mittens'));

        $mittensClient = $this->app->make(MittensClientInterface::class, ['client' => $client]);

        $this->expectException(MittensClientException::class);
        $mittensClient->post(new MittensRequest('/foo'));
    }

    private function getMittensClient(Client $client, ?MetricRepository $metricRepository = null): MittensClient
    {
        if ($metricRepository === null) {
            $metricRepository = $this->createMock(MetricRepository::class);
        }

        return new MittensClient(
            $client,
            $this->app->make(MetricService::class, ['metricRepository' => $metricRepository]),
            $this->createMock(LoggerInterface::class),
        );
    }
}
