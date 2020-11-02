<?php
declare(strict_types=1);

namespace DBCO\HealthAuthorityAPI\Tests\Application\Actions;

use DBCO\Shared\Application\Managers\TransactionManager;
use Exception;
use DBCO\HealthAuthorityAPI\Tests\TestCase;
use PDO;
use Ramsey\Uuid\Uuid;

/**
 * List case tasks tests.
 *
 * @package DBCO\HealthAuthorityAPI\Tests\Application\Actions
 */
class CaseActionTest extends TestCase
{
    /**
     * Set up.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->getAppInstance()->getContainer()->get(PDO::class)->beginTransaction();
    }

    /**
     * Tear down.
     */
    protected function tearDown(): void
    {
        $this->getAppInstance()->getContainer()->get(PDO::class)->rollBack();
        parent::tearDown();
    }

    /**
     * Test happy flow.
     *
     * @throws Exception
     */
    public function testGet()
    {
        $caseId = Uuid::uuid4();

        $pdo = $this->getAppInstance()->getContainer()->get(PDO::class);
        $pdo->query("
            INSERT INTO covidcase (uuid, owner, date_of_symptom_onset, status)
            VALUES ('{$caseId}', 'Test', TO_DATE('2020-10-30', 'YYYY-MM-DD'), 'open')
        ");

        $request = $this->createRequest('GET', '/v1/cases/' . $caseId);
        $response = $this->app->handle($request);
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Test what happens if we request a closed case.
     *
     * @throws Exception
     */
    public function testClosed()
    {
        $caseId = Uuid::uuid4();

        $pdo = $this->getAppInstance()->getContainer()->get(PDO::class);
        $pdo->query("
            INSERT INTO covidcase (uuid, owner, date_of_symptom_onset, status)
            VALUES ('{$caseId}', 'Test', TO_DATE('2020-10-30', 'YYYY-MM-DD'), 'closed')
        ");

        $request = $this->createRequest('GET', '/v1/cases/' . $caseId);
        $response = $this->app->handle($request);
        $this->assertEquals(404, $response->getStatusCode());
    }


    /**
     * Test non-existing case.
     *
     * @throws Exception
     */
    public function testWrongId()
    {
        $request = $this->createRequest('GET', '/v1/cases/1234');
        $response = $this->app->handle($request);
        $this->assertEquals(400, $response->getStatusCode());
    }
}

