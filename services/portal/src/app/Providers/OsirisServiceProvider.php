<?php

declare(strict_types=1);

namespace App\Providers;

use Ackintosh\Ganesha;
use Ackintosh\Ganesha\Builder;
use Ackintosh\Ganesha\Storage\Adapter\SlidingTimeWindowInterface;
use Ackintosh\Ganesha\Storage\AdapterInterface;
use App\Helpers\Config;
use App\Http\CircuitBreaker\CircuitBreaker;
use App\Http\CircuitBreaker\GaneshaCircuitBreaker;
use App\Http\Client\Soap\CircuitBreakerDecorator;
use App\Http\Client\Soap\NativeSoapClientFactory;
use App\Http\Client\Soap\SoapClient;
use App\Http\Client\Soap\SoapClientOptions;
use App\Http\Client\Soap\SoapClientProxy;
use App\Http\Server\Soap\SoapServer;
use App\Http\Server\Soap\SoapServerProxy;
use App\Jobs\ExportCaseToOsiris;
use App\Repositories\Osiris\CaseExportRepository;
use App\Repositories\Osiris\CredentialsRepository;
use App\Repositories\Osiris\DiskCredentialsRepository;
use App\Repositories\Osiris\SoapCaseExportRepository;
use App\Services\CircuitBreakerService;
use App\Services\Osiris\OsirisCaseExportStrategy;
use App\Services\Osiris\OsirisClient;
use App\Services\Osiris\OsirisSoapClient;
use App\Services\Osiris\SendDefinitiveAnswersStrategy;
use App\Services\Osiris\SendDeletedStatusStrategy;
use App\Services\Osiris\SendInitialAnswersStrategy;
use App\Services\Osiris\SoapMockService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Psr\Log\LoggerInterface;
use Webmozart\Assert\Assert;

use function assert;
use function sprintf;
use function str_contains;

final class OsirisServiceProvider extends ServiceProvider
{
    private const OSIRIS_SOAP_CLIENT = 'osiris.soap_client';
    private const OSIRIS_CIRCUIT_BREAKER = 'osiris.circuit_breaker';
    private const OSIRIS_GANESHA = 'osiris.ganesha';

    public function register(): void
    {
        $this->app->bind(CaseExportRepository::class, SoapCaseExportRepository::class);
        $this->app->bind(CredentialsRepository::class, DiskCredentialsRepository::class);
        $this->app->bind(NativeSoapClientFactory::class, NativeSoapClientFactory::class);

        // OsirisClient
        $this->app->bind(self::OSIRIS_SOAP_CLIENT, static function (): SoapClient {
            $configBaseUrl = Config::string('services.osiris.base_url');

            return new SoapClientProxy(
                new NativeSoapClientFactory(),
                wsdl: sprintf('%s%s', $configBaseUrl, str_contains($configBaseUrl, 'http') ? '?wsdl' : ''),
                soapClientOptions: new SoapClientOptions(
                    serviceName: Config::string('services.osiris.service_name'),
                    connectionTimeout: Config::integer('services.osiris.connection_timeout'),
                    timeout: Config::integer('services.osiris.timeout'),
                    cacheWsdl: Config::integer('services.osiris.cache_wsdl'),
                ),
            );
        });

        $this->app->bind(self::OSIRIS_GANESHA, static function (Application $app): Ganesha {
            $adapter = $app->get(AdapterInterface::class);
            Assert::isInstanceOf($adapter, AdapterInterface::class);
            Assert::isInstanceOf($adapter, SlidingTimeWindowInterface::class);

            $timeWindow = Config::integer('services.osiris.rate_strategy.time_window');
            Assert::greaterThanEq($timeWindow, 1);

            $failureRateThreshold = Config::integer('services.osiris.rate_strategy.failure_rate_threshold');
            Assert::range($failureRateThreshold, 1, 100);

            $minimumRequests = Config::integer('services.osiris.rate_strategy.minimum_requests');
            Assert::greaterThanEq($minimumRequests, 1);

            $intervalToHalfOpen = Config::integer('services.osiris.rate_strategy.interval_to_half_open');
            Assert::greaterThanEq($intervalToHalfOpen, 1);

            return Builder::withRateStrategy()
                ->timeWindow($timeWindow)
                ->failureRateThreshold($failureRateThreshold)
                ->minimumRequests($minimumRequests)
                ->intervalToHalfOpen($intervalToHalfOpen)
                ->adapter($adapter)
                ->build();
        });

        $this->app->bind(
            self::OSIRIS_CIRCUIT_BREAKER,
            static function (Application $app): CircuitBreaker {
                $ganesha = $app->get(self::OSIRIS_GANESHA);
                Assert::isInstanceOf($ganesha, Ganesha::class);

                return new GaneshaCircuitBreaker($ganesha);
            },
        );

        $this->app->extend(
            self::OSIRIS_SOAP_CLIENT,
            static function (SoapClient $soapClient, Application $app): SoapClient {
                $circuitBreaker = $app->get(self::OSIRIS_CIRCUIT_BREAKER);
                Assert::isInstanceOf($circuitBreaker, CircuitBreaker::class);

                $circuitBreakerService = $app->make(
                    CircuitBreakerService::class,
                    ['circuitBreaker' => $circuitBreaker],
                );
                Assert::isInstanceOf($circuitBreakerService, CircuitBreakerService::class);

                return new CircuitBreakerDecorator($soapClient, $circuitBreakerService);
            },
        );

        $this->app->bind(OsirisClient::class, OsirisSoapClient::class);
        $this->app->when(OsirisSoapClient::class)->needs(SoapClient::class)->give(self::OSIRIS_SOAP_CLIENT);

        $this->app->alias(SoapServerProxy::class, SoapServer::class);

        $this->app->bind(SoapMockService::class, static function (Application $app): SoapMockService {
            $soapServer = $app->get(SoapServer::class);
            Assert::isInstanceOf($soapServer, SoapServer::class);

            $wsdlPath = Config::string('services.osiris.mock_wsdl_path');
            $soapServer->setWsdl($wsdlPath);

            return new SoapMockService($soapServer, $wsdlPath);
        });

        $this->app->tag([
            SendInitialAnswersStrategy::class,
            SendDefinitiveAnswersStrategy::class,
            SendDeletedStatusStrategy::class,
        ], 'osiris.case_export_strategies');
    }

    public function boot(): void
    {
        RateLimiter::for(Config::string('services.osiris.rate_limit.rate_limiter_key'), static function () {
            return Limit::perMinute(
                Config::integer('services.osiris.rate_limit.max_jobs_per_minute'),
            );
        });

        $this->app->bindMethod([ExportCaseToOsiris::class, 'handle'], function (ExportCaseToOsiris $job): void {
            foreach ($this->app->tagged('osiris.case_export_strategies') as $strategy) {
                assert($strategy instanceof OsirisCaseExportStrategy);

                if ($strategy->supports($job->caseExportType)) {
                    $job->handle($this->app->make(LoggerInterface::class), $strategy);
                    return;
                }
            }
        });
    }
}
