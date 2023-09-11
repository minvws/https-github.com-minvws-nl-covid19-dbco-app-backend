<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\Bsn\BsnRepository;
use App\Repositories\Bsn\LocalBsnRepository;
use App\Repositories\Bsn\Mittens\MittensBsnRepository;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Webmozart\Assert\Assert;

use function sprintf;

class BsnServiceProvider extends ServiceProvider
{
    private Config $config;

    public function __construct(Application $app)
    {
        parent::__construct($app);

        $this->config = $this->app->make(Config::class);
    }

    /**
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function register(): void
    {
        /** @var string $configuredBsnProvider */
        $configuredBsnProvider = $this->config->get('services.bsn_provider');
        Assert::string($configuredBsnProvider);

        switch ($configuredBsnProvider) {
            case 'local':
                $this->bindLocalBsnRepository();
                break;
            case 'mittens':
                $this->bindMittensPseudoBsnRepository();
                break;
            default:
                throw new BindingResolutionException(sprintf('invalid bsn_provider: %s', $configuredBsnProvider));
        }
    }

    public function bindLocalBsnRepository(): void
    {
        $this->app->bind(BsnRepository::class, LocalBsnRepository::class);
    }

    /**
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     */
    private function bindMittensPseudoBsnRepository(): void
    {
        /** @var ?string $digidAccessTokensPath */
        $digidAccessTokensPath = $this->config->get('services.mittens.digid_access_tokens_path');
        Assert::nullOrString($digidAccessTokensPath);

        /** @var ?string $piiAccessTokensPath */
        $piiAccessTokensPath = $this->config->get('services.mittens.pii_access_tokens_path');
        Assert::nullOrString($piiAccessTokensPath);

        try {
            $digidAccessTokens = $this->readConfigFilesFromPath($digidAccessTokensPath);
            $piiAccessTokens = $this->readConfigFilesFromPath($piiAccessTokensPath);
        } catch (FileNotFoundException $fileNotFoundException) {
            throw new BindingResolutionException(
                $fileNotFoundException->getMessage(),
                $fileNotFoundException->getCode(),
                $fileNotFoundException,
            );
        }

        $this->app->when(MittensBsnRepository::class)->needs('$digidAccessTokens')->give($digidAccessTokens);
        $this->app->when(MittensBsnRepository::class)->needs('$piiAccessTokens')->give($piiAccessTokens);
        $this->app->when(MittensBsnRepository::class)
            ->needs('$tokensFor')
            ->giveConfig('services.mittens.pseudo_bsn_tokens_for');

        $this->app->bind(BsnRepository::class, MittensBsnRepository::class);
    }

    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    private function readConfigFilesFromPath(?string $accessTokensPath): array
    {
        if ($accessTokensPath === null) {
            throw new BindingResolutionException('mittens config path not set');
        }

        if (!File::exists($accessTokensPath)) {
            throw new FileNotFoundException(sprintf('mittens config file not found at: %s', $accessTokensPath));
        }

        return (array) File::getRequire($accessTokensPath);
    }
}
