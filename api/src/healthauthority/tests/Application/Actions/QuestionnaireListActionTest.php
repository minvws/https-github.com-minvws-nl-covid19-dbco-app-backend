<?php
declare(strict_types=1);

namespace DBCO\HealthAuthorityAPI\Tests\Application\Actions;

use DBCO\HealthAuthorityAPI\Application\Models\Question;
use DBCO\HealthAuthorityAPI\Application\Models\Questionnaire;
use DBCO\HealthAuthorityAPI\Application\Repositories\DbQuestionnaireRepository;
use DBCO\HealthAuthorityAPI\Application\Repositories\QuestionnaireRepository;
use Exception;
use DBCO\HealthAuthorityAPI\Tests\TestCase;
use PDO;
use Ramsey\Uuid\Uuid;

/**
 * List questionnaires test.
 *
 * @package DBCO\HealthAuthorityAPI\Tests\Application\Actions
 */
class QuestionnaireListActionTest extends TestCase
{
    private PDO $pdo;

    private QuestionnaireRepository $repository;

    /**
     * Set up.
     */
    protected function setUp(): void
    {
        parent::setUp();

//        $this->repository = $this->getAppInstance()->getContainer()->get(QuestionnaireRepository::class);
        $this->pdo = $this->getAppInstance()->getContainer()->get(PDO::class);
        $this->repository = new DbQuestionnaireRepository($this->pdo);
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
        $this->createTestQuestionnaires();
        $this->createTestQuestions();
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
print_r($data);
        $uuids = array_map(fn($q) => $q->uuid, $data->questionnaires);
        $this->assertNotContainsEquals('00000000-0000-0000-0000-000000000000', $uuids);
        $this->assertContainsEquals('00000000-0000-0000-0000-000000000001', $uuids);
        $this->assertContainsEquals('00000000-0000-0000-0000-000000000002', $uuids);
    }

    private function createTestQuestionnaires(): void
    {
        $questionnaire = new Questionnaire();
        $questionnaire->uuid = '00000000-0000-0000-0000-000000000000';
        $questionnaire->name = 'Test Questionnaire A';
        $questionnaire->taskType = 'contact';
        $questionnaire->version = 1;
        $this->repository->storeQuestionnaire($questionnaire);

        $questionnaire = new Questionnaire();
        $questionnaire->uuid = '00000000-0000-0000-0000-000000000001';
        $questionnaire->name = 'Test Questionnaire A';
        $questionnaire->taskType = 'contact';
        $questionnaire->version = 2;
        $this->repository->storeQuestionnaire($questionnaire);

        $questionnaire = new Questionnaire();
        $questionnaire->uuid = '00000000-0000-0000-0000-000000000002';
        $questionnaire->name = 'Test Questionnaire B';
        $questionnaire->taskType = 'other';
        $questionnaire->version = 3;
        $this->repository->storeQuestionnaire($questionnaire);
    }

    private function createTestQuestions()
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO question (
                uuid,
                questionnaire_uuid,
                "group",
                question_type,
                label,
                description,
                relevant_for_categories
            ) VALUES (
                :uuid,
                :questionnaire_uuid,
                :group,
                :question_type,
                :label,
                :description,
                :relevant_for_categories
            )
        ');

        $stmt->execute([
            'uuid' => '00000000-0000-0000-0001-000000000001',
            'questionnaire_uuid' => '00000000-0000-0000-0000-000000000001',
            'group' => "classification",
            'question_type' => 'classificationdetails',
            'label' => 'Vragen over jullie ontmoeting',
            'description' => null,
            'relevant_for_categories' => join(',', Question::ALL_CATEGORIES)
        ]);
    }
}
