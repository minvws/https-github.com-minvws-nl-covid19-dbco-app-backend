<?php

namespace DBCO\HealthAuthorityAPI\Application\Repositories;

use DateTimeImmutable;
use DBCO\HealthAuthorityAPI\Application\Models\AnswerOption;
use DBCO\HealthAuthorityAPI\Application\Models\ClassificationDetailsQuestion;
use DBCO\HealthAuthorityAPI\Application\Models\ContactDetailsQuestion;
use DBCO\HealthAuthorityAPI\Application\Models\DateQuestion;
use DBCO\HealthAuthorityAPI\Application\Models\MultipleChoiceQuestion;
use DBCO\HealthAuthorityAPI\Application\Models\OpenQuestion;
use DBCO\HealthAuthorityAPI\Application\Models\Question;
use DBCO\HealthAuthorityAPI\Application\Models\Questionnaire;
use DBCO\HealthAuthorityAPI\Application\Models\QuestionnaireList;
use PDO;

/**
 * Used for retrieving questionnaires from the database.
 *
 * @package DBCO\HealthAuthorityAPI\Application\Repositories
 */
class DbQuestionnaireRepository implements QuestionnaireRepository
{
    /**
     * @var PDO
     */
    private PDO $client;

    /**
     * Constructor.
     *
     * @param PDO $client
     */
    public function __construct(PDO $client)
    {
        $this->client = $client;
    }

    public function getQuestionnaires(): QuestionnaireList
    {
        $list = new QuestionnaireList();

        $stmt = $this->client->query('
            WITH latest AS (
                SELECT
                    uuid,
                    task_type,
                    version,
                    row_number() OVER (partition BY task_type ORDER BY version DESC) AS row_number
                FROM questionnaire
            )
            SELECT *
            FROM latest
            WHERE row_number = 1
        ');

        while ($row = $stmt->fetchObject()) {
            $questionnaire = new Questionnaire();
            $questionnaire->uuid = $row->uuid;
            $questionnaire->taskType = $row->task_type;
            $questionnaire->questions = $this->getQuestionsForQuestionnaire($questionnaire);
            $list->questionnaires[] = $questionnaire;
        }

        return $list;
    }

    /**
     * @param Questionnaire $questionnaire
     * @return Question[]
     */
    private function getQuestionsForQuestionnaire(Questionnaire $questionnaire): array
    {
        $questions = [];

        $stmt = $this->client->prepare('
            SELECT
                uuid,
                "group",
                question_type,
                label,
                description,
                relevant_for_categories
            FROM question
            WHERE questionnaire_uuid = :uuid
        ');

        $stmt->execute(['uuid' => $questionnaire->uuid]);
        while ($row = $stmt->fetchObject()) {
            $question = new Question;
            $question->uuid = $row->uuid;
            $question->group = $row->group;
            $question->questionType = $row->question_type;
            $question->label = $row->label;
            $question->description = $row->description;
            $question->relevantForCategories = explode(',', $row->relevant_for_categories);
            $questions[] = $question;
        }

        return $questions;
    }

    public function storeQuestionnaire(Questionnaire $questionnaire): void
    {
        $stmt = $this->client->prepare("
            INSERT INTO questionnaire (
                uuid,
                name,
                task_type,
                version
            ) VALUES (
                :uuid,
                :name,
                :task_type,
                :version
            )
        ");

        $res = $stmt->execute([
            'uuid' => $questionnaire->uuid,
            'name' => $questionnaire->name,
            'task_type' => $questionnaire->taskType,
            'version' => $questionnaire->version
        ]);

        foreach ($questionnaire->questions as $question) {
            $this->storeQuestionForQuestionnaire($questionnaire, $question);
        }
    }

    public function storeQuestionForQuestionnaire(Questionnaire $questionnaire, Question $question): void
    {
        $stmt = $this->client->prepare('
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

        $res = $stmt->execute([
            'uuid' => $question->uuid,
            'questionnaire_uuid' => $questionnaire->uuid,
            'group' => $question->group,
            'question_type' => $question->questionType,
            'label' => $question->label,
            'description' => $question->description,
            'relevant_for_categories' => join(',', $question->relevantForCategories)
        ]);
    }
}
