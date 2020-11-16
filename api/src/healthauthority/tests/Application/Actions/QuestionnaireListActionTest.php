<?php
declare(strict_types=1);

namespace DBCO\HealthAuthorityAPI\Tests\Application\Actions;

use DBCO\HealthAuthorityAPI\Application\Models\ClassificationDetailsQuestion;
use DBCO\HealthAuthorityAPI\Application\Models\Question;
use DBCO\HealthAuthorityAPI\Application\Models\Questionnaire;
use DBCO\HealthAuthorityAPI\Application\Repositories\DbQuestionnaireRepository;
use DBCO\HealthAuthorityAPI\Application\Repositories\QuestionnaireRepository;
use Exception;
use DBCO\HealthAuthorityAPI\Tests\TestCase;
use PDO;
use Ramsey\Uuid\Uuid;
use stdClass;

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
    }

    /**
     * Test happy flow.
     *
     * @throws Exception
     */
    public function testListOnlyRetrievesLatestVersionss()
    {
        $data = $this->retrieveQuestionnaireList();
        $this->assertObjectHasAttribute('questionnaires', $data);

        $uuids = array_map(fn($q) => $q->uuid, $data->questionnaires);
        $this->assertNotContainsEquals('00000000-0000-0000-0000-000000000000', $uuids);
        $this->assertContainsEquals('00000000-0000-0000-0000-000000000001', $uuids);
        $this->assertContainsEquals('00000000-0000-0000-0000-000000000002', $uuids);
    }

    public function testListReturnsQuestions()
    {
        $data = $this->retrieveQuestionnaireList();
        $this->assertObjectHasAttribute('questionnaires', $data);

        $questionnaire = $this->findByUuid('00000000-0000-0000-0000-000000000001', $data->questionnaires);
        $this->assertObjectHasAttribute('questions', $questionnaire);
        $this->assertCount(1, $questionnaire->questions);

        $question = $this->findByUuid('37d818ed-9499-4b9a-9771-725467368387', $questionnaire->questions);
        $this->assertSame('classification', $question->group);
        $this->assertSame('Vragen over jullie ontmoeting', $question->label);
        $this->assertSame('Beschrijvingsveld', $question->description);
        $this->assertSame(Question::ALL_CATEGORIES, array_map(fn($q) => $q->category, $question->relevantForCategories));
    }

    /**
     * Helper method to locate an object.uuid in  an array of data from the API
     * @param string $uuid
     * @param stdClass $jsonData
     * @return stdClass|null
     */
    private function findByUuid(string $uuid, array $jsonData): ?stdClass
    {
        foreach ($jsonData as $element) {
            if (isset($element->uuid) && $element->uuid === $uuid) {
                return $element;
            }
        }

        return null;
    }

    private function retrieveQuestionnaireList(): stdClass
    {
        $request = $this->createRequest('GET', '/v1/questionnaires');
        $response = $this->app->handle($request);
        if ($response->getStatusCode() !== 200) {
            print "***\n";
            print_r(str_replace('\n', "\n", (string)$response->getBody()));
        }
        $this->assertEquals(200, $response->getStatusCode());

        return json_decode((string)$response->getBody());
    }

    private function createTestQuestionnaires(): void
    {
        // Questionnaire A, old version
        $questionnaire = new Questionnaire();
        $questionnaire->uuid = '00000000-0000-0000-0000-000000000000';
        $questionnaire->name = 'Test Questionnaire A';
        $questionnaire->taskType = 'contact';
        $questionnaire->version = 1;
        $this->repository->storeQuestionnaire($questionnaire);

        // Questionnaire A, latest version
        $questionnaire = new Questionnaire();
        $questionnaire->uuid = '00000000-0000-0000-0000-000000000001';
        $questionnaire->name = 'Test Questionnaire A';
        $questionnaire->taskType = 'contact';
        $questionnaire->version = 2;
        $questionnaire->questions = [];

        $question1 = new ClassificationDetailsQuestion();
        $question1->uuid = "37d818ed-9499-4b9a-9771-725467368387";
        $question1->group = "classification";
        $question1->label = "Vragen over jullie ontmoeting";
        $question1->description = "Beschrijvingsveld";
        $question1->relevantForCategories = Question::ALL_CATEGORIES;
        $questionnaire->questions[] = $question1;

        $this->repository->storeQuestionnaire($questionnaire);

        // Questionnaire B
        $questionnaire = new Questionnaire();
        $questionnaire->uuid = '00000000-0000-0000-0000-000000000002';
        $questionnaire->name = 'Test Questionnaire B';
        $questionnaire->taskType = 'other';
        $questionnaire->version = 3;
        $this->repository->storeQuestionnaire($questionnaire);
    }
}
