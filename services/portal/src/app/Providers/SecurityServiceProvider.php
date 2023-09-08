<?php

declare(strict_types=1);

namespace App\Providers;

use App\Encryption\Security\SecurityCacheFake;
use App\Helpers\Environment;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Redis\Factory as RedisFactory;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use MinVWS\DBCO\Encryption\Security\EncryptionHelper;
use MinVWS\DBCO\Encryption\Security\HSMSecurityModule;
use MinVWS\DBCO\Encryption\Security\ProxySecurityCache;
use MinVWS\DBCO\Encryption\Security\RedisSecurityCache;
use MinVWS\DBCO\Encryption\Security\SecurityCache;
use MinVWS\DBCO\Encryption\Security\SecurityModule;
use MinVWS\DBCO\Encryption\Security\SimSecurityModule;
use Predis\Client as PredisClient;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Webmozart\Assert\Assert;

use function base64_decode;
use function is_string;

class SecurityServiceProvider extends ServiceProvider
{
    private Config $config;

    public function __construct(Application $app)
    {
        parent::__construct($app);

        $this->config = $this->app->make(Config::class);
    }

    public function register(): void
    {
        // phpcs:ignore Generic.Commenting.Todo.TaskFound -- baseline
        // TODO: FAKE CACHE MAKES TESTS BREAK
        if ($this->shouldUseFakeSecurityCache()) {
            $this->registerFakeSecurityCache();
        } else {
            $this->registerSecurityCache();
        }

        if ($this->config->get('securitymodule.type') === 'hsm') {
            $this->registerHSMSecurityModule();
        } else {
            $this->registerSimSecurityModule();
        }

        $this->app->singleton(EncryptionHelper::class);
    }

    private function registerHSMSecurityModule(): void
    {
        $this->app->singleton(SecurityModule::class, function (): SecurityModule {
            /** @var LoggerInterface $logger */
            $logger = $this->app->get(LoggerInterface::class);

            return new HSMSecurityModule($logger);
        });
    }

    private function registerSimSecurityModule(): void
    {
        $this->app->singleton(SecurityModule::class, function (): SecurityModule {
            $keyPath = $this->config->get('securitymodule.sim_key_path');
            Assert::string($keyPath);
            return new SimSecurityModule($keyPath);
        });
    }

    private function registerFakeSecurityCache(): void
    {
        $this->app
            ->when(SecurityCacheFake::class)
            ->needs('$key')
            ->give($this->getAppKey());

        $this->app->singleton(SecurityCache::class, function (): ProxySecurityCache {
            /** @var SecurityCacheFake $cache */
            $cache = $this->app->get(SecurityCacheFake::class);

            return new ProxySecurityCache($cache);
        });
    }

    private function registerSecurityCache(): void
    {
        $this->app->singleton(SecurityCache::class, static function (Application $app): ProxySecurityCache {
            /** @var RedisFactory $redisFactory */
            $redisFactory = $app->make(RedisFactory::class);

            /** @var PredisClient $redisClient */
            $redisClient = $redisFactory->connection('hsm')->client();

            return new ProxySecurityCache(new RedisSecurityCache($redisClient));
        });
    }

    private function shouldUseFakeSecurityCache(): bool
    {
        if (!Environment::isDevelopmentOrTesting()) {
            return false;
        }

        /** @var bool|string $useFake */
        $useFake = $this->config->get('security.useFakeHSM');

        return $useFake === true || $useFake === 'true';
    }

    private function getAppKey(): string
    {
        /** @var string $appKey */
        $appKey = $this->config->get('app.key');
        Assert::string($appKey);

        $decoded = base64_decode(Str::of($appKey)->remove('base64:')->toString(), true);
        if (!is_string($decoded)) {
            throw new RuntimeException('unable to decode appKey');
        }

        return $decoded;
    }
}
