<?php
declare(strict_types=1);

namespace Tests\Application\Actions;

use Exception;
use Firebase\JWT\JWT;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Request;
use Tests\TestCase;

/**
 * Register case tests.
 *
 * @package Tests\Application\Actions
 */
class RegisterCaseActionTest extends TestCase
{
    /**
     * Add authentication header to request.
     *
     * @param Request     $request
     * @param string|null $secret
     *
     * @return ServerRequestInterface
     */
    private function requestWithAuthorization(Request $request, ?string $secret = null): ServerRequestInterface
    {
        $payload = array(
            "iss" => "http://example.org",
            "aud" => "http://example.org",
            "iat" => time(),
            "exp" => time() + 300
        );

        $key = $secret ?? getenv('JWT_SECRET');

        $jwt = JWT::encode($payload, $key);

        return $request->withHeader('Authorization', 'Bearer ' . $jwt);
    }

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
        $request = $this->requestWithAuthorization($request);
        $response = $this->getAppInstance()->handle($request);
        $this->assertEquals(201, $response->getStatusCode());

        $payload = (string)$response->getBody();
        $decoded = json_decode($payload, true);
        $this->assertNotEmpty($decoded['pairingCode']);
        $this->assertISO8601ZuluDate($decoded['pairingCodeExpiresAt']);
    }

    /**
     * Test invalid authorization headers.
     *
     * @throws Exception
     */
    public function testInvalidAuthorization()
    {
        // missing authorization
        $request = $this->createRequest('POST', '/cases');
        $request = $request->withParsedBody(['caseId' => '123456', 'caseExpiresAt' => date('c', time() + 60)]);
        $request = $request->withHeader('Content-Type', 'application/json');
        $response = $this->getAppInstance()->handle($request);
        $this->assertEquals(401, $response->getStatusCode());

        // invalid authorization
        $request = $this->createRequest('POST', '/cases');
        $request = $request->withParsedBody(['caseId' => '123456', 'caseExpiresAt' => date('c', time() + 60)]);
        $request = $request->withHeader('Content-Type', 'application/json');
        $request = $request->withHeader('Authorization', 'Bearer this.is.not.correct');
        $response = $this->getAppInstance()->handle($request);
        $this->assertEquals(401, $response->getStatusCode());

        // invalid secret
        $request = $this->createRequest('POST', '/cases');
        $request = $request->withParsedBody(['caseId' => '123456', 'caseExpiresAt' => date('c', time() + 60)]);
        $request = $request->withHeader('Content-Type', 'application/json');
        $request = $this->requestWithAuthorization($request, 'not.the.correct.secret');
        $response = $this->getAppInstance()->handle($request);
        $this->assertEquals(401, $response->getStatusCode());
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
        $request = $this->requestWithAuthorization($request);
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

