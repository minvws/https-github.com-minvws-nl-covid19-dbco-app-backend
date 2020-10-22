<?php
declare(strict_types=1);

namespace DBCO\PrivateAPI\Tests;

use DBCO\Shared\Tests\TestCase as DBCOTestCase;
use Exception;
use Predis\Client as PredisClient;

/**
 * Base class for test cases.
 *
 * @package Tests
 */
class TestCase extends DBCOTestCase
{
    /**
     * Set up.
     *
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        // clear Redis
        $this->app->getContainer()->get(PredisClient::class)->flushall();
    }

    /**
     * Tear down.
     */
    protected function tearDown(): void
    {
        // clear Redis
        $this->app->getContainer()->get(PredisClient::class)->flushall();

        parent::tearDown();
    }
}
