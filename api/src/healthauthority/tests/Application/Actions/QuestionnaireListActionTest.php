<?php
declare(strict_types=1);

namespace DBCO\HealthAuthorityAPI\Tests\Application\Actions;

use DBCO\HealthAuthorityAPI\Application\Models\AnswerOption;
use DBCO\HealthAuthorityAPI\Application\Models\ClassificationDetailsQuestion;
use DBCO\HealthAuthorityAPI\Application\Models\ContactDetailsQuestion;
use DBCO\HealthAuthorityAPI\Application\Models\MultipleChoiceQuestion;
use DBCO\HealthAuthorityAPI\Application\Models\OpenQuestion;
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

    public function testQuestionnaireListContainsQuestions()
    {
        $data = $this->retrieveQuestionnaireList();
        $this->assertObjectHasAttribute('questionnaires', $data, 'No questionnaires returned');

        // Retrieve the latest version of test Questionnare A
        $questionnaire = $this->findByUuid('00000000-0000-0000-0000-000000000001', $data->questionnaires);

        // Does the API call return the questions?
        $this->assertObjectHasAttribute('questions', $questionnaire, 'Questionnaire does not contain any questions');
        $this->assertCount(5, $questionnaire->questions, 'Incorrect number of questions returned for questionnaire');

        // Check the first question to see if the data roundtrip works
        $question = $this->findByUuid('37d818ed-9499-4b9a-9771-725467368387', $questionnaire->questions);
        $this->assertNotNull($question);
        $this->assertSame('classification', $question->group);
        $this->assertSame('Vragen over jullie ontmoeting', $question->label);
        $this->assertSame('Beschrijvingsveld', $question->description);
        $this->assertSame(Question::ALL_CATEGORIES, array_map(fn($q) => $q->category, $question->relevantForCategories));

        // Check if the other questions are returned too
        $this->assertNotNull($this->findByUuid('37d818ed-9499-4b9a-9770-725467368388', $questionnaire->questions));
        $this->assertNotNull($this->findByUuid('37d818ed-9499-4b9a-9771-725467368389', $questionnaire->questions));
        $this->assertNotNull($this->findByUuid('37d818ed-9499-4b9a-9771-725467368390', $questionnaire->questions));
        $this->assertNotNull($this->findByUuid('37d818ed-9499-4b9a-9771-725467368391', $questionnaire->questions));
    }

    /**
     * Helper method to locate a stdClass.uuid in an array of API-data
     *
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

    /**
     * Convenience method to retrieve the questionnaires
     *
     * @return stdClass Decoded JSON data from the API
     */
    private function retrieveQuestionnaireList(): stdClass
    {
        $request = $this->createRequest('GET', '/v1/questionnaires');
        $response = $this->app->handle($request);
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

        $question2 = new ContactDetailsQuestion();
        $question2->uuid = "37d818ed-9499-4b9a-9770-725467368388";
        $question2->group = "contactdetails";
        $question2->label = "Contactgegevens";
        $question2->description = null;
        $question2->relevantForCategories = Question::ALL_CATEGORIES;;
        $questionnaire->questions[] = $question2;

        $question3 = new OpenQuestion();
        $question3->uuid = "37d818ed-9499-4b9a-9771-725467368389";
        $question3->group = "contactdetails";
        $question3->label = "Beroep";
        $question3->description = null;
        $question3->relevantForCategories = ["1"];
        $questionnaire->questions[] = $question3;

        $question4 = new MultipleChoiceQuestion();
        $question4->uuid = "37d818ed-9499-4b9a-9771-725467368390";
        $question4->group = "contactdetails";
        $question4->label = "Waar ken je deze persoon van?";
        $question4->description = null;
        $question4->relevantForCategories = [Question::CATEGORY_2A, Question::CATEGORY_2B];
        $question4->answerOptions = [
            new AnswerOption('Ouder', 'Ouder'),
            new AnswerOption('Kind', 'Kind'),
            new AnswerOption('Broer of zus', 'Broer of zus'),
            new AnswerOption('Partner', 'Partner'),
            new AnswerOption('Familielid (overig)', 'Familielid (overig)'),
            new AnswerOption('Huisgenoot', 'Huisgenoot'),
            new AnswerOption('Vriend of kennis', 'Vriend of kennis'),
            new AnswerOption('Medestudent of leerling', 'Medestudent of leerling'),
            new AnswerOption('Collega', 'Collega'),
            new AnswerOption('Gezondheidszorg medewerker', 'Gezondheidszorg medewerker'),
            new AnswerOption('Ex-partner', 'Ex-partner'),
            new AnswerOption('Overig', 'Overig')];
        $questionnaire->questions[] = $question4;

        $question5 = new MultipleChoiceQuestion();
        $question5->uuid = "37d818ed-9499-4b9a-9771-725467368391";
        $question5->group = "contactdetails";
        $question5->label = "Geldt een of meer van deze dingen voor deze persoon?";
        $question5->description =
            "<ul><li>Student</li>" .
            "<li>70 jaar of ouder</li>" .
            "<li>Gezondheidsklachten of extra gezondheidsrisico's</li>" .
            "<li>Woont in een zorginstelling of asielzoekerscentrum (bijvoorbeeld bejaardentehuis)</li>" .
            "<li>Spreekt slecht of geen Nederlands</li>" .
            "<li>Werkt in de zorg, onderwijs of een contactberoep (bijvoorbeeld kapper)</li></ul>";
        $question5->relevantForCategories = [
            Question::CATEGORY_1,
            Question::CATEGORY_2A,
            Question::CATEGORY_2B];
        $question5->answerOptions = [
            new AnswerOption('Ja, één of meerdere dingen', 'Ja', 'communication_staff'),
            new AnswerOption('Nee, ik denk het niet', 'Nee', 'communication_index')];
        $questionnaire->questions[] = $question5;

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
