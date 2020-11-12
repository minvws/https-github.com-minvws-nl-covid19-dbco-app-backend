<?php
declare(strict_types=1);

namespace DBCO\HealthAuthorityAPI\Tests\Application\Actions;

use Exception;
use DBCO\HealthAuthorityAPI\Tests\TestCase;
use PDO;

/**
 * List questionnaires test.
 *
 * @package DBCO\HealthAuthorityAPI\Tests\Application\Actions
 */
class QuestionnaireListActionTest extends TestCase
{
    private PDO $pdo;

    /**
     * Set up.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->pdo = $this->getAppInstance()->getContainer()->get(PDO::class);
        $this->pdo->beginTransaction();
        $this->createTestData();
    }

    /**
     * Tear down.
     */
    protected function tearDown(): void
    {
        $this->pdo->rollBack();
        parent::tearDown();
    }

    private function createTestData(): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO questionnaire (uuid, name, task_type, version) 
            VALUES (:uuid, :name, :task_type, :version)
        ");

        $stmt->execute([
            'uuid' => '00000000-0000-0000-0000-000000000000',
            'name' => 'Test Questionnaire A',
            'task_type' => 'contact',
            'version' => 1
        ]);

        $stmt->execute([
            'uuid' => '00000000-0000-0000-0000-000000000001',
            'name' => 'Test Questionnaire A',
            'task_type' => 'contact',
            'version' => 2
        ]);

        $stmt->execute([
            'uuid' => '00000000-0000-0000-0000-000000000002',
            'name' => 'Test Questionnaire B',
            'task_type' => 'other',
            'version' => 3
        ]);
    }

    /**
     * Test happy flow.
     *
     * @throws Exception
     */
    public function testListOnlyRetrievesLatestVersionss()
    {
        $request = $this->createRequest('GET', '/v1/questionnaires');
        $response = $this->app->handle($request);
        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode((string)$response->getBody());
        $this->assertObjectHasAttribute('questionnaires', $data);

        $uuids = array_map(fn($q) => $q->uuid, $data->questionnaires);
        $this->assertNotContainsEquals('00000000-0000-0000-0000-000000000000', $uuids);
        $this->assertContainsEquals('00000000-0000-0000-0000-000000000001', $uuids);
        $this->assertContainsEquals('00000000-0000-0000-0000-000000000002', $uuids);
    }
}
