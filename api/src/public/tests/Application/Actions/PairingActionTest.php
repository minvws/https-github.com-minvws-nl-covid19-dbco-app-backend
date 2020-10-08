<?php
declare(strict_types=1);

namespace Tests\Application\Actions;

use DBCO\Application\Models\DbcoCase;
use DBCO\Application\Models\Pairing;
use DBCO\Application\Repositories\CaseRepository;
use DBCO\Application\Repositories\PairingRepository;
use Exception;
use Tests\TestCase;

/**
 * Pairing tests.
 *
 * @package Tests\Application\Actions
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

        /** @var $caseRepository CaseRepository */
        $caseRepository = $this->app->getContainer()->get(CaseRepository::class);
        $case = new DbcoCase(self::CASE_ID, new \DateTime('+1 day'));
        $caseRepository->createCase($case);

        /** @var $pairingRepository PairingRepository */
        $pairingRepository = $this->app->getContainer()->get(PairingRepository::class);
        $pairing = new Pairing(null, $case, self::PAIRING_CODE, new \DateTime('+15 minutes'), false, null);
        $pairingRepository->createPairing($pairing);
        $this->assertNotNull($pairing->id);
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

