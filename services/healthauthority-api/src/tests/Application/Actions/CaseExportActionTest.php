<?php
declare(strict_types=1);

namespace DBCO\HealthAuthorityAPI\Tests\Application\Actions;

use DBCO\HealthAuthorityAPI\Application\Services\CaseService;
use Exception;
use DBCO\HealthAuthorityAPI\Tests\TestCase;

/**
 * Export case tests.
 *
 * @package DBCO\HealthAuthorityAPI\Tests\Application\Actions
 */
class CaseExportActionTest extends TestCase
{
    /**
     * Test happy flow.
     *
     * @throws Exception
     */
    public function testExport()
    {
        $caseUuid = '12345';

        $mockCaseService = $this->createMock(CaseService::class);
        $mockCaseService
            ->expects($this->once())
            ->method('exportCase')
            ->with($this->equalTo($caseUuid));

        $container = $this->getAppInstance()->getContainer();
        $container->set(CaseService::class, $mockCaseService);

        $request = $this->createRequest('POST', '/v1/cases/' . $caseUuid . '/exports');
        $response = $this->app->handle($request);
        $this->assertResponseStatusCode(204, $response);
        $this->assertEquals("", (string)$response->getBody());
    }
}

