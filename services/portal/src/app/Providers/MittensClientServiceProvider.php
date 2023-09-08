<?php

declare(strict_types=1);

namespace App\Providers;

use Ackintosh\Ganesha;
use Ackintosh\Ganesha\Builder;
use Ackintosh\Ganesha\Storage\Adapter\SlidingTimeWindowInterface;
use Ackintosh\Ganesha\Storage\Adapter\TumblingTimeWindowInterface;
use Ackintosh\Ganesha\Storage\AdapterInterface;
use App\Http\CircuitBreaker\CircuitBreaker;
use App\Http\CircuitBreaker\CircuitBreakerMiddleware;
use App\Http\CircuitBreaker\GaneshaCircuitBreaker;
use App\Http\Client\Guzzle\MittensClient;
use App\Http\Client\Guzzle\MittensClientInterface;
use App\Services\CircuitBreakerService;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\HandlerStack;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Webmozart\Assert\Assert;

class MittensClientServiceProvider extends ServiceProvider
{
    private const MITTENS_GANESHA = 'mittens.ganesha';
    private const MITTENS_CIRCUIT_BREAKER = 'mittens.circuit_breaker';
    private const MITTENS_CLIENT = 'mittens.client';

    private Config $config;

    public function __construct(Application $app)
    {
        parent::__construct($app);

        $this->config = $this->app->make(Config::class);
    }

    public function register(): void
    {
        $this->app->bind(self::MITTENS_GANESHA, function (Application $app): Ganesha {
            /** @var AdapterInterface&(SlidingTimeWindowInterface|TumblingTimeWindowInterface) $adapter */
            $adapter = $app->get(AdapterInterface::class);
            Assert::isInstanceOf($adapter, AdapterInterface::class);

            $takeWindow = $this->config->get('services.mittens.rate_strategy.time_window');
            Assert::numeric($takeWindow);

            $failureRateThreshold = $this->config->get('services.mittens.rate_strategy.failure_rate_threshold');
            Assert::numeric($failureRateThreshold);

            $minimumRequests = $this->config->get('services.mittens.rate_strategy.minimum_requests');
            Assert::numeric($minimumRequests);

            $intervalToHalfOpen = $this->config->get('services.mittens.rate_strategy.interval_to_half_open');
            Assert::numeric($intervalToHalfOpen);

            $takeWindow = (int) $takeWindow;
            Assert::positiveInteger($takeWindow);

            $failureRateThreshold = (int) $failureRateThreshold;
            Assert::positiveInteger($failureRateThreshold);
            Assert::range($failureRateThreshold, 1, 100);

            $minimumRequests = (int) $minimumRequests;
            Assert::positiveInteger($minimumRequests);

            $intervalToHalfOpen = (int) $intervalToHalfOpen;
            Assert::positiveInteger($intervalToHalfOpen);

            return Builder::withRateStrategy()
                ->timeWindow($takeWindow)
                ->failureRateThreshold($failureRateThreshold)
                ->minimumRequests($minimumRequests)
                ->intervalToHalfOpen($intervalToHalfOpen)
                ->adapter($adapter)
                ->build();
        });

        $this->app->bind(self::MITTENS_CIRCUIT_BREAKER, static function (Application $app): CircuitBreaker {
            $ganesha = $app->get(self::MITTENS_GANESHA);
            Assert::isInstanceOf($ganesha, Ganesha::class);

            return new GaneshaCircuitBreaker($ganesha);
        });

        $this->app->bind(
            self::MITTENS_CLIENT,
            function (Application $app): ClientInterface {
                $circuitBreaker = $app->get(self::MITTENS_CIRCUIT_BREAKER);
                Assert::isInstanceOf($circuitBreaker, CircuitBreaker::class);

                $circuitBreakerService = $app->make(
                    CircuitBreakerService::class,
                    ['circuitBreaker' => $circuitBreaker],
                );
                Assert::isInstanceOf($circuitBreakerService, CircuitBreakerService::class);

                $circuitBreakerMiddleware = $app->make(
                    CircuitBreakerMiddleware::class,
                    ['circuitBreakerService' => $circuitBreakerService],
                );
                Assert::isInstanceOf($circuitBreakerMiddleware, CircuitBreakerMiddleware::class);

                $handlerStack = HandlerStack::create();
                $handlerStack->push($circuitBreakerMiddleware);

                $options = $this->config->get('services.mittens.client_options', []);
                Assert::isArray($options);
                $options['handler'] = $handlerStack;

                return new Client($options);
            },
        );

        $this->app->when(MittensClient::class)
            ->needs(ClientInterface::class)
            ->give(self::MITTENS_CLIENT);

        $this->app->singleton(MittensClientInterface::class, MittensClient::class);
    }
}
