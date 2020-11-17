<?php
declare(strict_types=1);

namespace DBCO\Worker\Tests;

use DBCO\Worker\Application;
use Exception;
use PHPUnit\Framework\TestCase as PHPUnit_TestCase;
use Predis\Client as PredisClient;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class TestCase extends PHPUnit_TestCase
{
    /**
     * @var Application|null
     */
    protected ?Application $app = null;

    /**
     * Returns the per test app instance.
     *
     * @return Application
     *
     * @throws Exception
     */
    protected function getAppInstance(): Application
    {
        return $this->app;
    }

    /**
     * Create a new app instance.
     *
     * @return Application
     *
     * @throws Exception
     */
    protected function createAppInstance(): Application
    {
        $app = require __DIR__ . '/../bootstrap/application.php';
        $app->getContainer()->set(LoggerInterface::class, new NullLogger());
        return $app;
    }

    /**
     * Set up.
     *
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->app = $this->createAppInstance();

        $redis = $this->app->getContainer()->get(PredisClient::class);
        $redis->flushdb();
    }

    /**
     * Clean up.
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        $redis = $this->app->getContainer()->get(PredisClient::class);
        $redis->flushdb();

        $this->app = null;
    }
}
