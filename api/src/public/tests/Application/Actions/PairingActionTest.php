<?php
declare(strict_types=1);

namespace DBCO\PublicAPI\Tests\Application\Actions;

use DateTime;
use DBCO\PublicAPI\Application\Models\Pairing;
use DBCO\PublicAPI\Application\Repositories\PairingRepository;
use DI\Container;
use Exception;
use DBCO\PublicAPI\Tests\TestCase;
use Predis\Client as PredisClient;

/**
 * Pairing tests.
 *
 * @package DBCO\PublicAPI\Tests\Application\Actions
 */
class PairingActionTest extends TestCase
{
    private const CASE_ID = '12345';
    private const PAIRING_CODE = '123456789';

    /**
     * Set up.
     */
    protected function setUp(): void
    {
        parent::setUp();

        /** @var $redis PredisClient */
        $redis = $this->app->getContainer()->get(PredisClient::class);

        // store pairing request
        $pairingRequest = [
            'case' => [
                'id' => self::CASE_ID,
                'expiresAt' => (new DateTime('+1 day'))->format(DateTime::ATOM)
            ]
        ];

        $redis->setex('pairing-request:' . self::PAIRING_CODE, 60 * 15, json_encode($pairingRequest));
    }

    /**
     * Test happy flow.
     *
     * @throws Exception
     */
    public function testPairingAction()
    {
        $generalHAKeyPair = sodium_crypto_box_keypair();
        $generalHAPublicKey = sodium_crypto_box_publickey($generalHAKeyPair);

        $clientKeyPair = sodium_crypto_box_keypair();
        $clientPublicKey = sodium_crypto_box_publickey($clientKeyPair);

        $sealedClientPublicKey = sodium_crypto_box_seal($clientPublicKey, $generalHAPublicKey);

        $caseHAPublicKey = null;

        $stubPairingRepository = $this->createStub(PairingRepository::class);
        $stubPairingRepository->method('storePairing')->willReturnCallback(function (Pairing $pairing) use ($generalHAKeyPair, $clientPublicKey, &$caseHAPublicKey) {
            $caseHAKeyPair = sodium_crypto_box_keypair();
            $caseHAPublicKey = sodium_crypto_box_publickey($caseHAKeyPair);

            $unsealedClientPublicKey = sodium_crypto_box_seal_open($pairing->sealedClientPublicKey, $generalHAKeyPair);
            $this->assertEquals($clientPublicKey, $unsealedClientPublicKey);

            $sealedCaseHAPublicKey = sodium_crypto_box_seal($caseHAPublicKey, $unsealedClientPublicKey);
            $pairing->sealedHealthAuthorityPublicKey = $sealedCaseHAPublicKey;
        });

        /** @var Container $container */
        $container = $this->getAppInstance()->getContainer();
        $container->set(PairingRepository::class, $stubPairingRepository);

        // first time
        $request = $this->createRequest('POST', '/v1/pairings');
        $request = $request->withParsedBody(['pairingCode' => self::PAIRING_CODE, 'sealedClientPublicKey' => base64_encode($sealedClientPublicKey)]);
        $request = $request->withHeader('Content-Type', 'application/json');
        $response = $this->app->handle($request);
        $this->assertNotNull($caseHAPublicKey); // make sure our stub was called
        $this->assertEquals(201, $response->getStatusCode());
        $payload = (string)$response->getBody();
        $decoded = json_decode($payload, true);
        $this->assertNotEmpty($decoded['sealedHealthAuthorityPublicKey']);
        $healthAuthorityPublicKey = sodium_crypto_box_seal_open(base64_decode($decoded['sealedHealthAuthorityPublicKey']), $clientKeyPair);
        $this->assertNotFalse($healthAuthorityPublicKey);
        $this->assertEquals($caseHAPublicKey, $healthAuthorityPublicKey);

        // make sure a 2nd time doesn't work
        $request = $this->createRequest('POST', '/v1/pairings');
        $request = $request->withParsedBody(['pairingCode' => self::PAIRING_CODE, 'sealedClientPublicKey' => base64_encode($sealedClientPublicKey)]);
        $request = $request->withHeader('Content-Type', 'application/json');
        $response = $this->app->handle($request);
        $this->assertEquals(400, $response->getStatusCode());
        $payload = (string)$response->getBody();
        $data = json_decode($payload);
        $this->assertObjectHasAttribute('errors', $data);
        $this->assertCount(1, $data->errors);
        $this->assertEquals('invalid', $data->errors[0]->code);
        $this->assertEquals(['pairingCode'], $data->errors[0]->path);
    }

    /**
     * Test invalid requests.
     *
     * @throws Exception
     */
    public function testInvalidPairingAction()
    {
        $request = $this->createRequest('POST', '/v1/pairings');
        $request = $request->withParsedBody(['x' => 'y']);
        $request = $request->withHeader('Content-Type', 'application/json');
        $response = $this->app->handle($request);
        $this->assertEquals(400, $response->getStatusCode());

        $payload = (string)$response->getBody();
        $data = json_decode($payload);
        $this->assertObjectHasAttribute('errors', $data);
        $this->assertCount(2, $data->errors);
        $this->assertEquals('isRequired', $data->errors[0]->code);
        $this->assertEquals(['pairingCode'], $data->errors[0]->path);
        $this->assertEquals('isRequired', $data->errors[1]->code);
        $this->assertEquals(['sealedClientPublicKey'], $data->errors[1]->path);

    }
}

