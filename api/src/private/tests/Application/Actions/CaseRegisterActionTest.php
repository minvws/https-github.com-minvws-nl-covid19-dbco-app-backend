<?php
declare(strict_types=1);

namespace DBCO\PrivateAPI\Tests\Application\Actions;

use Exception;
use Firebase\JWT\JWT;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Request;
use DBCO\PrivateAPI\Tests\TestCase;

/**
 * Register case tests.
 *
 * @package DBCO\PrivateAPI\Tests\Application\Actions
 */
class CaseRegisterActionTest extends TestCase
{
    /**
     * Add authentication header to request.
     *
     * @param Request     $request
     * @param string      $caseId
     * @param string|null $secret
     *
     * @return ServerRequestInterface
     */
    private function requestWithAuthorization(Request $request, string $caseId, ?string $secret = null): ServerRequestInterface
    {
        $payload = array(
            "iat" => time(),
            "exp" => time() + 300,
            "http://ggdghor.nl/cid" => $caseId
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
        $caseId = base64_encode(random_bytes(16));

        $request = $this->createRequest('POST', '/v1/cases');
        $request = $request->withParsedBody(['caseId' => $caseId, 'caseExpiresAt' => date('c', time() + 60)]);
        $request = $request->withHeader('Content-Type', 'application/json');
        $request = $this->requestWithAuthorization($request, $caseId);
        $response = $this->app->handle($request);
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
        $caseId = base64_encode(random_bytes(16));

        // missing authorization
        $request = $this->createRequest('POST', '/v1/cases');
        $request = $request->withParsedBody(['caseId' => $caseId, 'caseExpiresAt' => date('c', time() + 60)]);
        $request = $request->withHeader('Content-Type', 'application/json');
        $response = $this->app->handle($request);
        $this->assertEquals(401, $response->getStatusCode());

        // invalid authorization
        $request = $this->createRequest('POST', '/v1/cases');
        $request = $request->withParsedBody(['caseId' => $caseId, 'caseExpiresAt' => date('c', time() + 60)]);
        $request = $request->withHeader('Content-Type', 'application/json');
        $request = $request->withHeader('Authorization', 'Bearer this.is.not.correct');
        $response = $this->app->handle($request);
        $this->assertEquals(401, $response->getStatusCode());

        // invalid secret
        $request = $this->createRequest('POST', '/v1/cases');
        $request = $request->withParsedBody(['caseId' => $caseId, 'caseExpiresAt' => date('c', time() + 60)]);
        $request = $request->withHeader('Content-Type', 'application/json');
        $request = $this->requestWithAuthorization($request, $caseId, 'not.the.correct.secret');
        $response = $this->app->handle($request);
        $this->assertEquals(401, $response->getStatusCode());
    }

    /**
     * Case ID in JWT claim doesn't match case ID in body.
     *
     * @throws Exception
     */
    public function testWrongCaseIdClaim()
    {
        $caseIdBody = base64_encode(random_bytes(16));
        $caseIdClaim = base64_encode(random_bytes(16));

        // wrong case ID in claim
        $request = $this->createRequest('POST', '/v1/cases');
        $request = $request->withParsedBody(['caseId' => $caseIdBody, 'caseExpiresAt' => date('c', time() + 60)]);
        $request = $request->withHeader('Content-Type', 'application/json');
        $request = $this->requestWithAuthorization($request, $caseIdClaim);
        $response = $this->app->handle($request);
        $this->assertEquals(400, $response->getStatusCode());

        $payload = (string)$response->getBody();
        $data = json_decode($payload);
        $this->assertCount(1, $data->errors);
        $this->assertEquals('invalid', $data->errors[0]->code);
        $this->assertEquals(['caseId'], $data->errors[0]->path);
    }

    /**
     * Test validation.
     *
     * @throws Exception
     */
    public function testMissingData()
    {
        $caseId = base64_encode(random_bytes(16));

        $request = $this->createRequest('POST', '/v1/cases');
        $request = $request->withHeader('Content-Type', 'application/json');
        $request = $this->requestWithAuthorization($request, $caseId);
        $response = $this->app->handle($request);

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

