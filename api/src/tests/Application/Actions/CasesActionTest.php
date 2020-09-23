<?php
declare(strict_types=1);

namespace Tests\Application\Actions;

use Tests\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class CasesActionTest extends TestCase
{
    public function testRegisterActionForResponse()
    {
        $app = $this->getAppInstance();
        $container = $app->getContainer();
        $container->set(LoggerInterface::class, new NullLogger());

        $request = $this->createRequest('POST', '/cases');
        $request = $request->withParsedBody(['caseId' => '123456']);
        $request = $request->withHeader('Content-Type', 'application/json');
        $response = $app->handle($request);

        $payload = (string) $response->getBody();
        $decoded = json_decode($payload, true);

        $this->assertEquals('123456', $decoded['caseId']);
        $this->assertNotEmpty($decoded['pairingCode']);
        $this->assertTrue($this->assertISO8601ZuluDate($decoded['pairingCodeExpiresAt']));
    }

    public function testCasesActionForMissingCaseId()
    {
        $app = $this->getAppInstance();
        $container = $app->getContainer();
        $container->set(LoggerInterface::class, new NullLogger());

        $request = $this->createRequest('POST', '/cases');
        $request = $request->withHeader('Content-Type', 'application/json');
        $response = $app->handle($request);

        $this->assertEquals(403, $response->getStatusCode());

        $payload = (string) $response->getBody();
        $decoded = json_decode($payload, true);

        $this->assertEquals('No caseId found in the request data', $decoded['description']);
    }


    private function assertISO8601ZuluDate($dateStr) {
        if (preg_match('/^([\+-]?\d{4}(?!\d{2}\b))((-?)((0[1-9]|1[0-2])(\3([12]\d|0[1-9]|3[01]))?|W([0-4]\d|5[0-2])(-?[1-7])?|(00[1-9]|0[1-9]\d|[12]\d{2}|3([0-5]\d|6[1-6])))([T\s]((([01]\d|2[0-3])((:?)[0-5]\d)?|24\:?00)([\.,]\d+(?!:))?)?(\17[0-5]\d([\.,]\d+)?)?[zZ])?)?$/', $dateStr) > 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

}

