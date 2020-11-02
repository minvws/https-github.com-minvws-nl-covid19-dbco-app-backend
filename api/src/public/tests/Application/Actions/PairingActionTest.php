<?php
declare(strict_types=1);

namespace DBCO\PublicAPI\Tests\Application\Actions;

use DateTime;
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
        $request = $this->createRequest('POST', '/v1/pairings');
        $request = $request->withParsedBody(['pairingCode' => self::PAIRING_CODE, 'deviceType' => 'android', 'deviceName' => 'Test']);
        $request = $request->withHeader('Content-Type', 'application/json');
        $response = $this->app->handle($request);
        $this->assertEquals(201, $response->getStatusCode());

        $payload = (string)$response->getBody();
        $decoded = json_decode($payload, true);
        $this->assertNotEmpty($decoded['case']);
        $this->assertEquals(self::CASE_ID, $decoded['case']['id']);
        $this->assertNotEmpty($decoded['signingKey']);
        $this->assertEquals(32, strlen(base64_decode($decoded['signingKey'])));

        // make sure a 2nd time doesn't work
        $request = $this->createRequest('POST', '/v1/pairings');
        $request = $request->withParsedBody(['pairingCode' => self::PAIRING_CODE, 'deviceType' => 'android', 'deviceName' => 'Test']);
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
        $request = $request->withParsedBody([]);
        $request = $request->withHeader('Content-Type', 'application/json');
        $response = $this->app->handle($request);
        $this->assertEquals(400, $response->getStatusCode());

        $payload = (string)$response->getBody();
        $data = json_decode($payload);
        $this->assertObjectHasAttribute('errors', $data);
        $this->assertCount(3, $data->errors);
        $this->assertEquals('isRequired', $data->errors[0]->code);
        $this->assertEquals(['pairingCode'], $data->errors[0]->path);
        $this->assertEquals('isRequired', $data->errors[1]->code);
        $this->assertEquals(['deviceType'], $data->errors[1]->path);
        $this->assertEquals('isRequired', $data->errors[2]->code);
        $this->assertEquals(['deviceName'], $data->errors[2]->path);

    }
}

