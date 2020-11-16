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
        $token = bin2hex(sodium_crypto_generichash(random_bytes(16)));

        $data = [
            'sealedCase' => [
                'ciphertext' => base64_encode(random_bytes(1024)),
                'nonce' => base64_encode(random_bytes(20)),
            ],
            'expiresAt' => gmdate(DATE_ISO8601, time() + 3600)
        ];

        // 1st time
        $request = $this->createRequest('PUT', '/v1/cases/' . $token);
        $request = $request->withParsedBody($data);
        $request = $request->withHeader('Content-Type', 'application/json');
        $request = $this->requestWithAuthorization($request, $token);
        $response = $this->app->handle($request);
        $this->assertEquals(204, $response->getStatusCode());

        // 2nd time should work just as well
        $request = $this->createRequest('PUT', '/v1/cases/' . $token);
        $request = $request->withParsedBody($data);
        $request = $request->withHeader('Content-Type', 'application/json');
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
        $token = bin2hex(sodium_crypto_generichash(random_bytes(16)));

        $data = [
            'sealedCase' => [
                'ciphertext' => base64_encode(random_bytes(1024)),
                'nonce' => base64_encode(random_bytes(20)),
            ],
            'expiresAt' => gmdate(DATE_ISO8601, time() + 3600)
        ];

        // missing authorization
        $request = $this->createRequest('PUT', '/v1/cases/' . $token);
        $request = $request->withParsedBody($data);
        $request = $request->withHeader('Content-Type', 'application/json');
        $response = $this->app->handle($request);
        $this->assertEquals(401, $response->getStatusCode());

        // invalid authorization
        $request = $this->createRequest('POST', '/v1/cases');
        $request = $request->withParsedBody($data);
        $request = $request->withHeader('Content-Type', 'application/json');
        $request = $request->withHeader('Authorization', 'Bearer this.is.not.correct');
        $response = $this->app->handle($request);
        $this->assertEquals(401, $response->getStatusCode());

        // invalid secret
        $request = $this->createRequest('PUT', '/v1/cases/' . $token);
        $request = $request->withParsedBody($data);
        $request = $request->withHeader('Content-Type', 'application/json');
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
        $tokenUrl = bin2hex(sodium_crypto_generichash(random_bytes(16)));
        $tokenClaim = bin2hex(sodium_crypto_generichash(random_bytes(16)));

        $data = [
            'sealedCase' => [
                'ciphertext' => base64_encode(random_bytes(1024)),
                'nonce' => base64_encode(random_bytes(20)),
            ],
            'expiresAt' => gmdate(DATE_ISO8601, time() + 3600)
        ];

        // wrong token in claim
        $request = $this->createRequest('PUT', '/v1/cases/' . $tokenUrl);
        $request = $request->withParsedBody($data);
        $request = $request->withHeader('Content-Type', 'application/json');
        $request = $this->requestWithAuthorization($request, $tokenClaim);
        $response = $this->app->handle($request);

        $this->assertEquals(400, $response->getStatusCode());

        $payload = (string)$response->getBody();
        $data = json_decode($payload);
        $this->assertCount(1, $data->errors);
        $this->assertEquals('invalid', $data->errors[0]->code);
        $this->assertEquals(['$url', 'token'], $data->errors[0]->path);
    }
}

