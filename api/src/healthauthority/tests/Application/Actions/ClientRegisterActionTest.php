<?php
declare(strict_types=1);

namespace DBCO\HealthAuthorityAPI\Tests\Application\Actions;

use Exception;
use DBCO\HealthAuthorityAPI\Tests\TestCase;
use PDO;
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
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->getAppInstance()->getContainer()->get(PDO::class)->beginTransaction();
    }

    /**
     * Tear down.
     */
    protected function tearDown(): void
    {
        $this->getAppInstance()->getContainer()->get(PDO::class)->rollBack();
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

        $encodedGeneralKeyPair = getenv('ENCRYPTION_GENERAL_KEY_PAIR');
        $this->assertNotEmpty($encodedGeneralKeyPair);
        $generalKeyPair = base64_decode($encodedGeneralKeyPair);
        $generalPublicKey = sodium_crypto_box_publickey($generalKeyPair);

        $clientKeyPair = sodium_crypto_kx_keypair();
        $clientPublicKey = sodium_crypto_kx_publickey($clientKeyPair);

        $sealedClientPublicKey = base64_encode(sodium_crypto_box_seal($clientPublicKey, $generalPublicKey));

        $request = $this->createRequest('POST', '/v1/cases/' . $caseUuid . '/clients');
        $request = $request->withParsedBody([ 'sealedClientPublicKey' => $sealedClientPublicKey ]);
        $request = $request->withHeader('Content-Type', 'application/json');
        $response = $this->app->handle($request);
        $this->assertEquals(201, $response->getStatusCode());

        $data = json_decode((string)$response->getBody());
        $this->assertNotFalse($data);
        $this->assertIsObject($data);
        $this->assertNotEmpty($data->sealedHealthAuthorityPublicKey);

        $healthAuthorityPublicKey = sodium_crypto_box_seal_open(base64_decode($data->sealedHealthAuthorityPublicKey), $clientKeyPair);
        $this->assertNotFalse($healthAuthorityPublicKey);
    }
}

