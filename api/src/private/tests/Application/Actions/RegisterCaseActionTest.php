<?php
declare(strict_types=1);

namespace Tests\Application\Actions;

use Exception;
use Tests\TestCase;

/**
 * Register case tests.
 *
 * @package Tests\Application\Actions
 */
class RegisterCaseActionTest extends TestCase
{
    /**
     * Test happy flow.
     *
     * @throws Exception
     */
    public function testRegisterAction()
    {
        $request = $this->createRequest('POST', '/cases');
        $request = $request->withParsedBody(['caseId' => '123456', 'caseExpiresAt' => date('c', time() + 60)]);
        $request = $request->withHeader('Content-Type', 'application/json');
        $response = $this->getAppInstance()->handle($request);

        $payload = (string)$response->getBody();
        $decoded = json_decode($payload, true);
var_dump($payload);die;
        $this->assertNotEmpty($decoded['pairingCode']);
        $this->assertISO8601ZuluDate($decoded['pairingCodeExpiresAt']);
    }

    /**
     * Test validation.
     *
     * @throws Exception
     */
    public function testInvalidRegisterAction()
    {
        $request = $this->createRequest('POST', '/cases');
        $request = $request->withHeader('Content-Type', 'application/json');
        $response = $this->getAppInstance()->handle($request);

        $this->assertEquals(400, $response->getStatusCode());

        $payload = (string)$response->getBody();
        $data = json_decode($payload);
        $this->assertObjectHasAttribute('errors', $data);
        $this->assertCount(2, $data->errors);
        $this->assertEquals('isRequired', $data->errors[0]->code);
        $this->assertEquals(['caseId'], $data->errors[0]->path);
        $this->assertEquals('isRequired', $data->errors[1]->code);
        $this->assertEquals(['caseExpiresAt'], $data->errors[1]->path);
    }
}

