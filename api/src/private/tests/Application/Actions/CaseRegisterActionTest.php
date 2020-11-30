<?php
declare(strict_types=1);

namespace DBCO\PrivateAPI\Tests\Application\Actions;

use Exception;
use Firebase\JWT\JWT;
use Predis\Client as PredisClient;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;
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
     * @param string      $caseUuid
     * @param string|null $secret
     *
     * @return ServerRequestInterface
     */
    private function requestWithAuthorization(Request $request, string $caseUuid, ?string $secret = null): ServerRequestInterface
    {
        $payload = array(
            "iat" => time(),
            "exp" => time() + 300,
            "http://ggdghor.nl/caseUuid" => $caseUuid
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
        $caseUuid = Uuid::uuid4()->toString();

        // first time
        $request = $this->createRequest('POST', '/v1/cases');
        $request = $request->withParsedBody(['caseUuid' => $caseUuid]);
        $request = $request->withHeader('Content-Type', 'application/json');
        $request = $this->requestWithAuthorization($request, $caseUuid);
        $response = $this->app->handle($request);
        $this->assertResponseStatusCode(201, $response);

        $payload = (string)$response->getBody();
        $decoded = json_decode($payload, true);
        $attempt1PairingCode = $decoded['pairingCode'] ?? null;
        $this->assertNotEmpty($attempt1PairingCode);
        $this->assertISO8601ZuluDate($decoded['pairingCodeExpiresAt']);

        $redis = $this->getAppInstance()->getContainer()->get(PredisClient::class);
        $this->assertEquals(1, $redis->exists('pairing-request:' . $attempt1PairingCode));
        $this->assertEquals(1, $redis->exists('pairing-request:' . $attempt1PairingCode . ':case'));
        $this->assertEquals(1, $redis->exists('case:' . $caseUuid . ':pairing-request'));
        $this->assertEquals(['code' => $attempt1PairingCode], json_decode($redis->get('case:' . $caseUuid . ':pairing-request'), true));

        // second time should work as well, the case data for the old pairing code should however
        // not exist anymore, plus the new pairing code should be different
        $request = $this->createRequest('POST', '/v1/cases');
        $request = $request->withParsedBody(['caseUuid' => $caseUuid]);
        $request = $request->withHeader('Content-Type', 'application/json');
        $request = $this->requestWithAuthorization($request, $caseUuid);
        $response = $this->app->handle($request);
        $this->assertResponseStatusCode(201, $response);

        $payload = (string)$response->getBody();
        $decoded = json_decode($payload, true);
        $attempt2PairingCode = $decoded['pairingCode'] ?? null;
        $this->assertNotEmpty($attempt2PairingCode);

        $this->assertNotEquals($attempt1PairingCode, $attempt2PairingCode);

        $redis = $this->getAppInstance()->getContainer()->get(PredisClient::class);
        $this->assertEquals(1, $redis->exists('pairing-request:' . $attempt1PairingCode));
        $this->assertEquals(0, $redis->exists('pairing-request:' . $attempt1PairingCode . ':case'));
        $this->assertEquals(1, $redis->exists('pairing-request:' . $attempt2PairingCode));
        $this->assertEquals(1, $redis->exists('pairing-request:' . $attempt2PairingCode . ':case'));
        $this->assertEquals(1, $redis->exists('case:' . $caseUuid . ':pairing-request'));
        $this->assertEquals(['code' => $attempt2PairingCode], json_decode($redis->get('case:' . $caseUuid . ':pairing-request'), true));
    }

    /**
     * Test invalid authorization headers.
     *
     * @throws Exception
     */
    public function testInvalidAuthorization()
    {
        $caseUuid = Uuid::uuid4()->toString();

        // missing authorization
        $request = $this->createRequest('POST', '/v1/cases');
        $request = $request->withParsedBody(['caseUuid' => $caseUuid]);
        $request = $request->withHeader('Content-Type', 'application/json');
        $response = $this->app->handle($request);
        $this->assertResponseStatusCode(401, $response);

        // invalid authorization
        $request = $this->createRequest('POST', '/v1/cases');
        $request = $request->withParsedBody(['caseUuid' => $caseUuid]);
        $request = $request->withHeader('Content-Type', 'application/json');
        $request = $request->withHeader('Authorization', 'Bearer this.is.not.correct');
        $response = $this->app->handle($request);
        $this->assertResponseStatusCode(401, $response);

        // invalid secret
        $request = $this->createRequest('POST', '/v1/cases');
        $request = $request->withParsedBody(['caseUuid' => $caseUuid]);
        $request = $request->withHeader('Content-Type', 'application/json');
        $request = $this->requestWithAuthorization($request, $caseUuid, 'not.the.correct.secret');
        $response = $this->app->handle($request);
        $this->assertResponseStatusCode(401, $response);
    }

    /**
     * Case UUID in JWT claim doesn't match case UUID in body.
     *
     * @throws Exception
     */
    public function testWrongCaseUuidClaim()
    {
        $caseUuidBody = Uuid::uuid4()->toString();
        $caseUuidClaim = Uuid::uuid4()->toString();

        // wrong case ID in claim
        $request = $this->createRequest('POST', '/v1/cases');
        $request = $request->withParsedBody(['caseUuid' => $caseUuidBody]);
        $request = $request->withHeader('Content-Type', 'application/json');
        $request = $this->requestWithAuthorization($request, $caseUuidClaim);
        $response = $this->app->handle($request);
        $this->assertResponseStatusCode(400, $response);

        $payload = (string)$response->getBody();
        $data = json_decode($payload);
        $this->assertCount(1, $data->errors);
        $this->assertEquals('invalid', $data->errors[0]->code);
        $this->assertEquals(['caseUuid'], $data->errors[0]->path);
    }

    /**
     * Test validation.
     *
     * @throws Exception
     */
    public function testMissingData()
    {
        $caseUuid = Uuid::uuid4()->toString();

        $request = $this->createRequest('POST', '/v1/cases');
        $request = $request->withHeader('Content-Type', 'application/json');
        $request = $this->requestWithAuthorization($request, $caseUuid);
        $response = $this->app->handle($request);
        $this->assertResponseStatusCode(400, $response);

        $payload = (string)$response->getBody();
        $data = json_decode($payload);
        $this->assertObjectHasAttribute('errors', $data);
        $this->assertCount(1, $data->errors);
        $this->assertEquals('isRequired', $data->errors[0]->code);
        $this->assertEquals(['caseUuid'], $data->errors[0]->path);
    }
}

