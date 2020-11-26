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
    private const CASE_UUID = '7376989b-edf4-4c45-90a5-0324f0ef9c0f';
    private const PAIRING_CODE = '123456789';

    /**
     * Test happy flow.
     *
     * @throws Exception
     */
    public function testPairingAction()
    {
        $this->registerPairingRequestInRedis();

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
        $this->assertResponseStatusCode(201, $response);
        $this->assertNotNull($caseHAPublicKey); // make sure our stub was called
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
        $this->assertResponseStatusCode(400, $response);
        $payload = (string)$response->getBody();
        $data = json_decode($payload);
        $this->assertObjectHasAttribute('errors', $data);
        $this->assertCount(1, $data->errors);
        $this->assertEquals('invalid', $data->errors[0]->code);
        $this->assertEquals(['pairingCode'], $data->errors[0]->path);
    }


    /**
     * Test expired warning.
     *
     * @throws Exception
     */
    public function testExpiredWarningAction()
    {
        $stubPairingRepository = $this->createStub(PairingRepository::class);
        $stubPairingRepository->method('storePairing')->willReturnCallback(function (Pairing $pairing) use ($generalHAKeyPair, $clientPublicKey, &$caseHAPublicKey) {
            $pairing->sealedHealthAuthorityPublicKey = random_bytes(SODIUM_CRYPTO_KX_PUBLICKEYBYTES);
        });

        /** @var Container $container */
        $container = $this->getAppInstance()->getContainer();
        $container->set(PairingRepository::class, $stubPairingRepository);

        $this->registerPairingRequestInRedis(self::PAIRING_CODE, self::CASE_UUID, 1, 5, 10);

        // wait until expired
        sleep(2);

        // first time should give an expired warning
        $request = $this->createRequest('POST', '/v1/pairings');
        $request = $request->withParsedBody(['pairingCode' => self::PAIRING_CODE, 'sealedClientPublicKey' => base64_encode(random_bytes(100))]);
        $request = $request->withHeader('Content-Type', 'application/json');
        $response = $this->app->handle($request);
        $this->assertResponseStatusCode(400, $response);
        $payload = (string)$response->getBody();
        $data = json_decode($payload);
        $this->assertObjectHasAttribute('errors', $data);
        $this->assertCount(1, $data->errors);
        $this->assertEquals('expired', $data->errors[0]->code);
        $this->assertEquals(['pairingCode'], $data->errors[0]->path);

        // second time should give the normal invalid error
        $request = $this->createRequest('POST', '/v1/pairings');
        $request = $request->withParsedBody(['pairingCode' => self::PAIRING_CODE, 'sealedClientPublicKey' => base64_encode(random_bytes(100))]);
        $request = $request->withHeader('Content-Type', 'application/json');
        $response = $this->app->handle($request);
        $this->assertResponseStatusCode(400, $response);
        $payload = (string)$response->getBody();
        $data = json_decode($payload);
        $this->assertObjectHasAttribute('errors', $data);
        $this->assertCount(1, $data->errors);
        $this->assertEquals('invalid', $data->errors[0]->code);
        $this->assertEquals(['pairingCode'], $data->errors[0]->path);

        // register again, this time check the expired warning timeout
        $this->registerPairingRequestInRedis(self::PAIRING_CODE, self::CASE_UUID, 1, 1, 10);

        // wait until expired warning should not be returned anymore
        sleep(2);

        // should get the invalid warning right away
        $request = $this->createRequest('POST', '/v1/pairings');
        $request = $request->withParsedBody(['pairingCode' => self::PAIRING_CODE, 'sealedClientPublicKey' => base64_encode(random_bytes(100))]);
        $request = $request->withHeader('Content-Type', 'application/json');
        $response = $this->app->handle($request);
        $this->assertResponseStatusCode(400, $response);
        $payload = (string)$response->getBody();
        $data = json_decode($payload);
        $this->assertObjectHasAttribute('errors', $data);
        $this->assertCount(1, $data->errors);
        $this->assertEquals('invalid', $data->errors[0]->code);
        $this->assertEquals(['pairingCode'], $data->errors[0]->path);
    }

    /**
     * Test pairing process through Redis.
     *
     * @throws Exception
     */
    public function testPairingThroughRedisBridgeAction()
    {
        $this->registerPairingRequestInRedis();

        $generalHAKeyPair = sodium_crypto_box_keypair();
        $generalHAPublicKey = sodium_crypto_box_publickey($generalHAKeyPair);

        $clientKeyPair = sodium_crypto_box_keypair();
        $clientPublicKey = sodium_crypto_box_publickey($clientKeyPair);
        $sealedClientPublicKey = sodium_crypto_box_seal($clientPublicKey, $generalHAPublicKey);

        // we already store the pairing response in Redis to verify the list is read
        $redis = $this->getAppInstance()->getContainer()->get(PredisClient::class);
        $dummySealedHealthAuthorityPublicKey = random_bytes(32);
        $pairingResponse = [
            'status' => 'SUCCESS',
            'data' => json_encode([
                'sealedHealthAuthorityPublicKey' => base64_encode($dummySealedHealthAuthorityPublicKey)
            ])
        ];
        $redis->rpush('client-response:' . self::CASE_UUID, json_encode($pairingResponse));

        $request = $this->createRequest('POST', '/v1/pairings');
        $request = $request->withParsedBody(['pairingCode' => self::PAIRING_CODE, 'sealedClientPublicKey' => base64_encode($sealedClientPublicKey)]);
        $request = $request->withHeader('Content-Type', 'application/json');
        $response = $this->app->handle($request);
        $this->assertResponseStatusCode(201, $response);
        $payload = (string)$response->getBody();
        $decoded = json_decode($payload, true);
        $this->assertNotEmpty($decoded['sealedHealthAuthorityPublicKey']);
        $this->assertEquals($dummySealedHealthAuthorityPublicKey, base64_decode($decoded['sealedHealthAuthorityPublicKey']));
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
        $this->assertResponseStatusCode(400, $response);

        $payload = (string)$response->getBody();
        $data = json_decode($payload);
        $this->assertObjectHasAttribute('errors', $data);
        $this->assertCount(2, $data->errors);
        $this->assertEquals('isRequired', $data->errors[0]->code);
        $this->assertEquals(['pairingCode'], $data->errors[0]->path);
        $this->assertEquals('isRequired', $data->errors[1]->code);
        $this->assertEquals(['sealedClientPublicKey'], $data->errors[1]->path);

    }

    /**
     * Register pairing request in Redis.
     *
     * NOTE:
     * We can't use the private API for this because we have no control
     * over the used expiry delta's. So it is very important to keep this
     * code compatible with the private API code!
     *
     * @param string    $code
     * @param string    $caseUuid
     * @param int       $expiresDelta
     * @param float|int $expiredWarningDelta
     * @param float|int $blockedDelta
     */
    private function registerPairingRequestInRedis(
        $code = self::PAIRING_CODE,
        $caseUuid = self::CASE_UUID,
        $expiresDelta = 900,
        $expiredWarningDelta = 24 * 60 * 60,
        $blockedDelta = 30 * 24 * 60 * 60
    )
    {
        $redis = $this->app->getContainer()->get(PredisClient::class);

        $pairingRequestData = ['codeBlockedUntil' => date(DATE_ATOM, time() + $blockedDelta)];
        $redis->setex('pairing-request:' . $code, $blockedDelta, json_encode($pairingRequestData));

        $pairingRequestCaseData = [
            'caseUuid' => $caseUuid,
            'codeExpiresAt' => date(DATE_ATOM, time() + $expiresDelta),
            'codeExpiredWarningUntil' => date(DATE_ATOM, time() + $expiredWarningDelta)
        ];
        $redis->setex('pairing-request:' . $code . ':case', $expiredWarningDelta, json_encode($pairingRequestCaseData));

        $casePairingRequestData = ['code' => $code];
        $redis->setex('case:' . $code . ':pairing-request', $expiredWarningDelta, json_encode($casePairingRequestData));
    }
}

