<?php
declare(strict_types=1);

namespace DBCO\HealthAuthorityAPI\Tests\Application\Actions;

use DBCO\HealthAuthorityAPI\Application\Security\SecurityCache;
use DBCO\HealthAuthorityAPI\Application\Security\SecurityModule;
use DBCO\HealthAuthorityAPI\Application\Services\SecurityService;
use Exception;
use DBCO\HealthAuthorityAPI\Tests\TestCase;
use PDO;
use Predis\Client as PredisClient;
use Ramsey\Uuid\Uuid;

/**
 * Register client tests.
 *
 * @package DBCO\HealthAuthorityAPI\Tests\Application\Actions
 */
class ClientRegisterActionTest extends TestCase
{
    /**
     * Set up.
     *
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->getAppInstance()->getContainer()->get(PDO::class)->beginTransaction();
        $this->getAppInstance()->getContainer()->get(PredisClient::class)->flushall();
        $this->getAppInstance()->getContainer()->get(SecurityService::class)->createKeyExchangeSecretKey();
        $this->getAppInstance()->getContainer()->get(SecurityService::class)->manageStoreSecretKeys(fn () => null);
    }

    /**
     * Tear down.
     */
    protected function tearDown(): void
    {
        $this->getAppInstance()->getContainer()->get(PDO::class)->rollBack();
        $this->getAppInstance()->getContainer()->get(PredisClient::class)->flushall();
        parent::tearDown();
    }

    /**
     * Test happy flow.
     *
     * @throws Exception
     */
    public function testRegister()
    {
        $caseUuid = Uuid::uuid4();
        $dateOfSymptomOnset = date('Y-m-d', strtotime('yesterday'));
        $windowExpiresAt = date('Y-m-d', strtotime('+2 days'));

        $pdo = $this->getAppInstance()->getContainer()->get(PDO::class);
        $pdo->query("
            INSERT INTO covidcase (uuid, owner, date_of_symptom_onset, window_expires_at, status)
            VALUES ('{$caseUuid}', 'Test', '{$dateOfSymptomOnset}', '{$windowExpiresAt}', 'open')
        "); // NOTE: Oracle might need TO_DATE call (untested)

        $generalSecretKey = $this->getAppInstance()->getContainer()->get(SecurityCache::class)->getSecretKey(SecurityModule::SK_KEY_EXCHANGE);
        $this->assertNotEmpty($generalSecretKey);
        $generalPublicKey = sodium_crypto_box_publickey_from_secretkey($generalSecretKey);

        $clientKeyPair = sodium_crypto_kx_keypair();
        $clientPublicKey = sodium_crypto_kx_publickey($clientKeyPair);

        $sealedClientPublicKey = base64_encode(sodium_crypto_box_seal($clientPublicKey, $generalPublicKey));

        $request = $this->createRequest('POST', '/v1/cases/' . $caseUuid . '/clients');
        $request = $request->withParsedBody([ 'sealedClientPublicKey' => $sealedClientPublicKey ]);
        $request = $request->withHeader('Content-Type', 'application/json');
        $response = $this->app->handle($request);
        $this->assertResponseStatusCode(201, $response);

        $data = json_decode((string)$response->getBody());
        $this->assertNotFalse($data);
        $this->assertIsObject($data);
        $this->assertNotEmpty($data->sealedHealthAuthorityPublicKey);

        $healthAuthorityPublicKey = sodium_crypto_box_seal_open(base64_decode($data->sealedHealthAuthorityPublicKey), $clientKeyPair);
        $this->assertNotFalse($healthAuthorityPublicKey);
    }
}

