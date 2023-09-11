<?php

declare(strict_types=1);

namespace Tests\Unit\Providers;

use App\Providers\BsnServiceProvider;
use App\Repositories\Bsn\BsnRepository;
use App\Repositories\Bsn\LocalBsnRepository;
use App\Repositories\Bsn\Mittens\MittensBsnRepository;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

use function config;

class BsnServiceProviderTest extends TestCase
{
    public function testRegisterLocalBsnRepository(): void
    {
        config()->set('services.bsn_provider', 'local');

        $bsnServiceProvider = new BsnServiceProvider($this->app);
        $bsnServiceProvider->register();

        $bsnRepository = $this->app->get(BsnRepository::class);

        $this->assertInstanceOf(LocalBsnRepository::class, $bsnRepository);
    }

    public function testRegisterMittensBsnRepository(): void
    {
        $digidAccessTokensPath = 'digid_access_tokens_path';
        $piiAccessTokensPath = 'pii_access_tokens_path';

        config()->set('services.bsn_provider', 'mittens');
        config()->set('services.mittens.digid_access_tokens_path', $digidAccessTokensPath);
        config()->set('services.mittens.pii_access_tokens_path', $piiAccessTokensPath);

        File::expects('exists')
            ->with($digidAccessTokensPath)
            ->andReturn(true);
        File::expects('getRequire')
            ->with($digidAccessTokensPath)
            ->andReturn([
                '01003' => 'digid_01003',
            ]);

        File::expects('exists')
            ->with($piiAccessTokensPath)
            ->andReturn(true);
        File::expects('getRequire')
            ->with($piiAccessTokensPath)
            ->andReturn([
                '01003' => 'pii_01003',
            ]);

        $bsnServiceProvider = new BsnServiceProvider($this->app);
        $bsnServiceProvider->register();

        $bsnRepository = $this->app->get(BsnRepository::class);

        $this->assertInstanceOf(MittensBsnRepository::class, $bsnRepository);
    }

    public function testRegisterMittensBsnRepositoryDigidAccessTokensPathNotSet(): void
    {
        config()->set('services.bsn_provider', 'mittens');
        config()->set('services.mittens.digid_access_tokens_path', null);

        $bsnServiceProvider = new BsnServiceProvider($this->app);

        $this->expectException(BindingResolutionException::class);
        $this->expectExceptionMessage('mittens config path not set');
        $bsnServiceProvider->register();
    }

    public function testRegisterMittensBsnRepositoryPiiAccessTokensPathNotSet(): void
    {
        $digidAccessTokensPath = 'foo';

        config()->set('services.bsn_provider', 'mittens');
        config()->set('services.mittens.digid_access_tokens_path', $digidAccessTokensPath);
        config()->set('services.mittens.pii_access_tokens_path', null);

        $bsnServiceProvider = new BsnServiceProvider($this->app);

        File::expects('exists')
            ->with($digidAccessTokensPath)
            ->andReturn(true);
        File::expects('getRequire')
            ->with($digidAccessTokensPath)
            ->andReturn([]);

        $this->expectException(BindingResolutionException::class);
        $this->expectExceptionMessage('mittens config path not set');
        $bsnServiceProvider->register();
    }

    public function testRegisterMittensBsnRepositoryDigidAccessTokensPathNotFound(): void
    {
        $digidAccessTokensPath = 'foo';

        config()->set('services.bsn_provider', 'mittens');
        config()->set('services.mittens.digid_access_tokens_path', $digidAccessTokensPath);

        $bsnServiceProvider = new BsnServiceProvider($this->app);

        File::expects('exists')
            ->with($digidAccessTokensPath)
            ->andReturn(false);

        $this->expectException(BindingResolutionException::class);
        $this->expectExceptionMessage('mittens config file not found at: foo');
        $bsnServiceProvider->register();
    }

    public function testRegisterInvalidBsnRepository(): void
    {
        config()->set('services.bsn_provider', 'foo');

        $bsnServiceProvider = new BsnServiceProvider($this->app);

        $this->expectException(BindingResolutionException::class);
        $this->expectExceptionMessage('invalid bsn_provider: foo');
        $bsnServiceProvider->register();
    }
}
