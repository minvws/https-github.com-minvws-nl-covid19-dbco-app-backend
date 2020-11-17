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
            switch ($row->question_type) {
                case 'classificationdetails':
                    $question = new ClassificationDetailsQuestion();
                    break;
                case 'contactdetails':
                    $question = new ContactDetailsQuestion();
                    break;
                case 'date':
                    $question = new DateQuestion();
                    break;
                case 'multiplechoice':
                    $question = new MultipleChoiceQuestion();
                    break;
                case 'open':
                    $question = new OpenQuestion();
                    break;
            }

            $question->uuid = $row->uuid;
            $question->group = $row->group;
            $question->questionType = $row->question_type;
            $question->label = $row->label;
            $question->description = $row->description;
            $question->relevantForCategories = explode(',', $row->relevant_for_categories);

            if ($question instanceof MultipleChoiceQuestion) {
                $question->answerOptions = $this->getAnswerOptionsForQuestion($question);
            }

            $questions[] = $question;
        }

        return $questions;
    }

    /**
     * @param Question $question
     * @return AnswerOption[]
     */
    public function getAnswerOptionsForQuestion(Question $question): array
    {
        $options = [];

        $stmt = $this->client->prepare('
            SELECT
                uuid,
                question_uuid,
                label,
                value,
                trigger
            FROM answer_option
            WHERE question_uuid = :question_uuid
        ');

        $stmt->execute(['question_uuid' => $question->uuid]);
        while ($row = $stmt->fetchObject()) {
            $options[] = new AnswerOption(
                $row->label,
                $row->value,
                $row->trigger
            );
        }

        return $options;
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

        if ($question instanceof MultipleChoiceQuestion) {
            foreach ($question->answerOptions as $option) {
                $this->storeAnswerOptionForQuestion($question, $option);
            }
        }
    }

    public function storeAnswerOptionForQuestion(Question $question, AnswerOption $option): void
    {
        $stmt = $this->client->prepare('
            INSERT INTO answer_option (
                uuid,
                question_uuid,
                label,
                value,
                trigger
            ) VALUES (
                :uuid,
                :question_uuid,
                :label,
                :value,
                :trigger
            )
        ');

        $res = $stmt->execute([
            'uuid' => $option->uuid,
            'question_uuid' => $question->uuid,
            'label' => $option->label,
            'value' => $option->value,
            'trigger' => $option->trigger,
        ]);
    }
}
