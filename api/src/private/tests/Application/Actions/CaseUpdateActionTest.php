<?php
declare(strict_types=1);

namespace DBCO\PrivateAPI\Tests\Application\Actions;

use Exception;
use Firebase\JWT\JWT;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Request;
use DBCO\PrivateAPI\Tests\TestCase;

/**
 * Update case tests.
 *
 * @package DBCO\PrivateAPI\Tests\Application\Actions
 */
class CaseUpdateActionTest extends TestCase
{
    /**
     * Add authentication header to request.
     *
     * @param Request     $request
     * @param string      $token
     * @param string|null $secret
     *
     * @return ServerRequestInterface
     */
    private function requestWithAuthorization(Request $request, string $token, ?string $secret = null): ServerRequestInterface
    {
        $payload = array(
            "iat" => time(),
            "exp" => time() + 300,
            "http://ggdghor.nl/token" => $token
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
    public function testUpdateAction()
    {
        $token = hash_hmac('sha256', random_bytes(16), 'secret');

        // 1st time
        $payload = random_bytes(1024);
        $request = $this->createRequest('PUT', '/v1/cases/' . $token);
        $request->getBody()->write($payload);
        $request = $request->withHeader('Expires', gmdate('D, d M Y H:i:s \G\M\T', time() + 3600));
        $request = $this->requestWithAuthorization($request, $token);
        $response = $this->app->handle($request);
        $this->assertEquals(204, $response->getStatusCode());

        // 2nd time should work just as well
        $payload = random_bytes(1024);
        $request = $this->createRequest('PUT', '/v1/cases/' . $token);
        $request->getBody()->write($payload);
        $request = $request->withHeader('Expires', gmdate('D, d M Y H:i:s \G\M\T', time() + 3600));
        $request = $this->requestWithAuthorization($request, $token);
        $response = $this->app->handle($request);
        $this->assertEquals(204, $response->getStatusCode());

    }

    /**
     * Test invalid authorization headers.
     *
     * @throws Exception
     */
    public function testInvalidAuthorization()
    {
        $token = hash_hmac('sha256', random_bytes(16), 'secret');
        $payload = random_bytes(1024);

        // missing authorization
        $request = $this->createRequest('PUT', '/v1/cases/' . $token);
        $request->getBody()->write($payload);
        $request = $request->withHeader('Content-Type', 'application/json');
        $request = $request->withHeader('Expires', gmdate('D, d M Y H:i:s \G\M\T', time() + 3600));
        $response = $this->app->handle($request);
        $this->assertEquals(401, $response->getStatusCode());

        // invalid authorization
        $request = $this->createRequest('POST', '/v1/cases');
        $request->getBody()->write($payload);
        $request = $request->withHeader('Content-Type', 'application/json');
        $request = $request->withHeader('Expires', gmdate('D, d M Y H:i:s \G\M\T', time() + 3600));
        $request = $request->withHeader('Authorization', 'Bearer this.is.not.correct');
        $response = $this->app->handle($request);
        $this->assertEquals(401, $response->getStatusCode());

        // invalid secret
        $request = $this->createRequest('PUT', '/v1/cases/' . $token);
        $request->getBody()->write($payload);
        $request = $request->withHeader('Expires', gmdate('D, d M Y H:i:s \G\M\T', time() + 3600));
        $request = $this->requestWithAuthorization($request, $token, 'not.the.correct.secret');
        $response = $this->app->handle($request);
        $this->assertEquals(401, $response->getStatusCode());
    }

    /**
     * Token n JWT claim doesn't match token in URL.
     *
     * @throws Exception
     */
    public function testWrongTokenClaim()
    {
        $tokenUrl = hash_hmac('sha256', random_bytes(16), 'secret');
        $tokenClaim = hash_hmac('sha256', random_bytes(16), 'secret');

        $payload = random_bytes(1024);

        // wrong token in claim
        $request = $this->createRequest('PUT', '/v1/cases/' . $tokenUrl);
        $request->getBody()->write($payload);
        $request = $request->withHeader('Expires', gmdate('D, d M Y H:i:s \G\M\T', time() + 3600));
        $request = $this->requestWithAuthorization($request, $tokenClaim);
        $response = $this->app->handle($request);

        $this->assertEquals(400, $response->getStatusCode());

        $payload = (string)$response->getBody();
        $data = json_decode($payload);
        $this->assertCount(1, $data->errors);
        $this->assertEquals('invalid', $data->errors[0]->code);
        $this->assertEquals(['token'], $data->errors[0]->path);
    }
}

