<?php
declare(strict_types=1);

namespace DBCO\HealthAuthorityAPI\Tests\Application\Services;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use DBCO\HealthAuthorityAPI\Application\Security\SecurityModule;
use DBCO\HealthAuthorityAPI\Application\Services\SecurityService;
use DBCO\Shared\Application\Helpers\DateTimeHelper;
use Exception;
use DBCO\HealthAuthorityAPI\Tests\TestCase;
use Predis\Client as PredisClient;

/**
 * Security service test
 *
 * @package DBCO\HealthAuthorityAPI\Tests\Application\Services
 */
class SecurityServiceTest extends TestCase
{
    /**
     * @var DateTimeImmutable
     */
    private DateTimeImmutable $now;

    /**
     * Reset secrets.
     *
     * @throws Exception
     */
    private function resetSecrets()
    {
        $securityModule = $this->getAppInstance()->getContainer()->get(SecurityModule::class);
        $secretKeyIdentifiers = $securityModule->listSecretKeys();
        foreach ($secretKeyIdentifiers as $identifier) {
            $securityModule->deleteSecretKey($identifier);
        }

        $this->assertEquals([], $securityModule->listSecretKeys());

        $this->getAppInstance()->getContainer()->get(PredisClient::class)->flushall();
    }

    /**
     * Set up.
     *
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->now = new DateTimeImmutable('now', new DateTimeZone('Europe/Amsterdam'));
        $mockDateTimeHelper = $this->createMock(DateTimeHelper::class);
        $mockDateTimeHelper->method('now')->willReturnCallback(fn () => $this->now);

        $container = $this->getAppInstance()->getContainer();
        $container->set(DateTimeHelper::class, $mockDateTimeHelper);

        $this->resetSecrets();
    }

    /**
     * Tear down.
     *
     * @throws Exception
     */
    protected function tearDown(): void
    {
        $this->resetSecrets();
        parent::tearDown();
    }

    /**
     * Test creation of key exchange secret key.
     *
     * @throws Exception
     */
    public function testCreateKeyExchangeSecretKey()
    {
        $securityService = $this->getAppInstance()->getContainer()->get(SecurityService::class);

        // public key should not exist yet
        $publicKey = $securityService->getKeyExchangePublicKey();
        $this->assertNull($publicKey);

        // create secret key
        $result = $securityService->createKeyExchangeSecretKey();
        $this->assertEquals(true, $result);

        // public key should exist
        $publicKey = $securityService->getKeyExchangePublicKey();
        $this->assertNotNull($publicKey);

        // second time should not work
        $result = $securityService->createKeyExchangeSecretKey();
        $this->assertEquals(false, $result);

        // public key should remain the same
        $publicKey2 = $securityService->getKeyExchangePublicKey();
        $this->assertNotNull($publicKey2);
        $this->assertEquals($publicKey, $publicKey2);

        // but with force it should work
        $result = $securityService->createKeyExchangeSecretKey(true);
        $this->assertEquals(true, $result);

        // public key should exist and be different
        $publicKey3 = $securityService->getKeyExchangePublicKey();
        $this->assertNotNull($publicKey3);
        $this->assertNotEquals($publicKey, $publicKey3);
    }


    /**
     * Test store secret key management.
     *
     * @throws Exception
     */
    public function testManageStoreSecretKeys()
    {
        $securityService = $this->getAppInstance()->getContainer()->get(SecurityService::class);
        $tz = new DateTimeZone('Europe/Amsterdam');

        // set current date to 2021-01-05
        $this->now = new DateTimeImmutable('2021-01-05', $tz);

        // keys for all days should be created
        $days1 = [];
        $currentDay1 = $securityService->manageStoreSecretKeys(function (DateTimeInterface $day, string $mutation) use (&$days1) {
            $days1[] = ['day' => $day, 'mutation' => $mutation];
        });

        $this->assertEquals('2021-01-05', $currentDay1->format('Y-m-d'));
        $this->assertCount(14, $days1);
        foreach ($days1 as $day) {
            $this->assertEquals(SecurityService::MUTATION_CREATED, $day['mutation']);
        }
        $this->assertEquals('2020-12-23', $days1[0]['day']->format('Y-m-d'));
        $this->assertEquals('2020-12-24', $days1[1]['day']->format('Y-m-d'));
        $this->assertEquals('2021-01-04', $days1[12]['day']->format('Y-m-d'));
        $this->assertEquals('2021-01-05', $days1[13]['day']->format('Y-m-d'));

        // second time the existing keys should be detected and loaded
        $days2 = [];
        $currentDay2 = $securityService->manageStoreSecretKeys(function (DateTimeInterface $day, string $mutation) use (&$days2) {
            $days2[] = ['day' => $day, 'mutation' => $mutation];
        });

        $this->assertEquals('2021-01-05', $currentDay2->format('Y-m-d'));
        $this->assertCount(14, $days2);
        foreach ($days2 as $day) {
            $this->assertEquals(SecurityService::MUTATION_LOADED, $day['mutation']);
        }
        $this->assertEquals('2020-12-23', $days2[0]['day']->format('Y-m-d'));
        $this->assertEquals('2020-12-25', $days2[2]['day']->format('Y-m-d'));
        $this->assertEquals('2021-01-03', $days2[11]['day']->format('Y-m-d'));
        $this->assertEquals('2021-01-05', $days2[13]['day']->format('Y-m-d'));

        // when passing the current day, no mutations should be necessary
        $days3 = [];
        $currentDay3 = $securityService->manageStoreSecretKeys(function (DateTimeInterface $day, string $mutation) use (&$days3) {
            $days3[] = ['day' => $day, 'mutation' => $mutation];
        }, $currentDay1);

        $this->assertEquals('2021-01-05', $currentDay3->format('Y-m-d'));
        $this->assertCount(0, $days3);

        // change the current day to the next day
        $this->now = new DateTimeImmutable('2021-01-06', $tz);

        // when passing the current day, 2 mutations should be detected (remove the oldest day, create a new day)
        $days4 = [];
        $currentDay4 = $securityService->manageStoreSecretKeys(function (DateTimeInterface $day, string $mutation) use (&$days4) {
            $days4[] = ['day' => $day, 'mutation' => $mutation];
        }, $currentDay1);

        $this->assertEquals('2021-01-06', $currentDay4->format('Y-m-d'));
        $this->assertCount(2, $days4);
        $this->assertEquals('2020-12-23', $days4[0]['day']->format('Y-m-d'));
        $this->assertEquals(SecurityService::MUTATION_DELETED, $days4[0]['mutation']);
        $this->assertEquals('2021-01-06', $days4[1]['day']->format('Y-m-d'));
        $this->assertEquals(SecurityService::MUTATION_CREATED, $days4[1]['mutation']);

        // calling a second time should detect 0 mutations
        $days5 = [];
        $currentDay5 = $securityService->manageStoreSecretKeys(function (DateTimeInterface $day, string $mutation) use (&$days5) {
            $days5[] = ['day' => $day, 'mutation' => $mutation];
        }, $currentDay1);

        $this->assertEquals('2021-01-06', $currentDay5->format('Y-m-d'));
        $this->assertCount(0, $days5);
    }
}

