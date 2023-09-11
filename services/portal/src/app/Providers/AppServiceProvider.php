<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Purpose\Purpose;
use App\Models\Purpose\SubPurpose;
use App\Providers\Auth\IdentityHubClient;
use App\Providers\Auth\IdentityHubProvider;
use App\Schema\Documentation\Documentation;
use App\Schema\Documentation\LaravelDocumentationProvider;
use App\Schema\Purpose\PurposeSpecificationConfig;
use Carbon\CarbonImmutable;
use GuzzleHttp\Client;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Console\Kernel as ConsoleKernel;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Migrations\MigrationCreator;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\ParallelTesting;
use Illuminate\Support\ServiceProvider;
use Laravel\Socialite\Contracts\Factory;
use Laravel\Socialite\SocialiteManager;
use Webmozart\Assert\Assert;

class AppServiceProvider extends ServiceProvider
{
    private Config $config;

    public function __construct(Application $app)
    {
        parent::__construct($app);

        $this->config = $this->app->make(Config::class);
    }

    public function register(): void
    {
        $this->app->singleton(Migrator::class, static fn(Application $app): Migrator => $app->make('migrator'));
        $this->app->singleton(
            MigrationCreator::class,
            static fn(Application $app): MigrationCreator => $app->make('migration.creator')
        );
    }

    /**
     * @throws BindingResolutionException
     */
    public function boot(): void
    {
        Documentation::setProvider(new LaravelDocumentationProvider('schema'));
        Paginator::useBootstrap();
        $this->bootIdentityHubSocialite();

        $purposeConfig = new PurposeSpecificationConfig(Purpose::class, SubPurpose::class);
        PurposeSpecificationConfig::setConfig($purposeConfig);

        ParallelTesting::setUpTestDatabase(function (): void {
            /** @var ConsoleKernel $artisan */
            $artisan = $this->app->make(ConsoleKernel::class);

            $artisan->call('db:seed');
        });

        Date::use(CarbonImmutable::class);
    }

    /**
     * @throws BindingResolutionException
     */
    private function bootIdentityHubSocialite(): void
    {
        $config = $this->config->get('services.identityhub');

        Assert::isArray($config);

        $this->app->singleton(
            IdentityHubClient::class,
            static fn(): Client => new Client($config['guzzle'] ?? []),
        );

        /** @var SocialiteManager $socialite */
        $socialite = $this->app->make(Factory::class);
        $socialite->extend(
            'identityhub',
            static function (Application $app) use ($socialite, $config): IdentityHubProvider {
                /** @var Client&IdentityHubClient $client */
                $client = $app->make(IdentityHubClient::class);

                Assert::isInstanceOf($client, Client::class);

                /** @var IdentityHubProvider $identityHubProvider */
                $identityHubProvider = $socialite->buildProvider(IdentityHubProvider::class, $config);
                $identityHubProvider->setHttpClient($client);

                return $identityHubProvider;
            },
        );
    }
}
