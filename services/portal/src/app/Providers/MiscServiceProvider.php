<?php

declare(strict_types=1);

namespace App\Providers;

use App\Helpers\Config;
use App\Http\Controllers\Api\Export\SchemaLocationResolver;
use App\Services\AuthenticationService;
use App\Services\AuthorizationService;
use App\Services\BcoNumber\BcoNumberGenerator;
use App\Services\BcoNumber\BcoNumberService;
use App\Services\Export\Helpers\ExportCursorHelper;
use App\Services\Factory\TimelineDtoFactory;
use App\Services\JsonSchema\JsonSchemaValidator;
use App\Services\JsonSchema\SwaggestJsonSchemaValidator;
use App\Services\TestResult\TestResultReportImportService;
use App\Services\TestResult\TestResultReportImportServiceInterface;
use App\Services\TestResult\TestResultReportImportServiceMetricDecorator;
use DBCO\Shared\Application\Metrics\Transformers\EventTransformer;
use Illuminate\Container\Container;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Connection;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\ServiceProvider;
use MinVWS\HealthCheck\Checks\PDOHealthCheck;
use MinVWS\HealthCheck\Checks\PredisHealthCheck;
use MinVWS\HealthCheck\HealthChecker;
use MinVWS\Metrics\Transformers\EventTransformer as EventTransformerInterface;
use PDO;
use Webmozart\Assert\Assert;

use function base_path;

class MiscServiceProvider extends ServiceProvider
{
    private ConfigRepository $config;

    public function __construct(Application $app)
    {
        parent::__construct($app);

        $this->config = $this->app->make(ConfigRepository::class);
    }

    public function register(): void
    {
        $this->app->bind(EventTransformerInterface::class, EventTransformer::class);
        $this->app->when(EventTransformer::class)
            ->needs(PDO::class)
            ->give($this->getPdoFromDefaultConnection(...));

        $this->app->bind(HealthChecker::class, function (Application $app) {
            $healthChecker = new HealthChecker();

            $healthChecker->addHealthCheck(
                'redis-haa',
                new PredisHealthCheck(static fn () => Redis::connection()->client()),
            );
            $healthChecker->addHealthCheck(
                'mysql',
                new PDOHealthCheck(fn () => $this->getPdoFromDefaultConnection($app)),
            );

            return $healthChecker;
        });

        $this->app->singleton(AuthorizationService::class);
        $this->app->singleton(AuthenticationService::class);

        $this->app
            ->when(TimelineDtoFactory::class)
            ->needs('$displayTimezone')
            ->giveConfig('app.display_timezone');

        $this->app->bind(BcoNumberService::class, function (Container $app) {
            $maxRetries = $this->config->get('misc.bcoNumbers.maxRetries');
            Assert::numeric($maxRetries);

            /** @var string $allowedNumberChars */
            $allowedNumberChars = $this->config->get('misc.bcoNumbers.allowedNumberChars');
            Assert::string($allowedNumberChars);

            /** @var string $allowedAlphaChars */
            $allowedAlphaChars = $this->config->get('misc.bcoNumbers.allowedAlphaChars');
            Assert::string($allowedAlphaChars);

            $bcoNumberGenerator = new BcoNumberGenerator($allowedNumberChars, $allowedAlphaChars);

            return new BcoNumberService((int) $maxRetries, $bcoNumberGenerator);
        });

        $this->app->bind(TestResultReportImportServiceInterface::class, TestResultReportImportService::class);
        $this->app->extend(
            TestResultReportImportService::class,
            static function (TestResultReportImportService $testResultReportImportService, Container $app) {
                return $app->make(
                    TestResultReportImportServiceMetricDecorator::class,
                    ['decorated' => $testResultReportImportService],
                );
            },
        );

        $this->app->when(ExportCursorHelper::class)
            ->needs('$jwtSecret')
            ->giveConfig('security.exportCursorJwtSecret');

        $this->app->when(SchemaLocationResolver::class)
            ->needs('$basePath')
            ->give(base_path(Config::string('schema.output.json')));

        $this->app->bind(JsonSchemaValidator::class, SwaggestJsonSchemaValidator::class);
    }

    private function getPdoFromDefaultConnection(Application $app): PDO
    {
        /** @var ConnectionResolverInterface $db */
        $db = $app->make(ConnectionResolverInterface::class);

        /** @var Connection $connection */
        $connection = $db->connection();

        Assert::isInstanceOf($connection, Connection::class);

        return $connection->getPdo();
    }
}
