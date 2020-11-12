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
            with latest as (
                select *,
                row_number() over (partition by task_type order by version desc) as row_number
                from questionnaire
            )
            select *
            from latest
            where row_number = 1
        ');

        while ($row = $stmt->fetchObject()) {
            $questionnaire = new Questionnaire();
            $questionnaire->uuid = $row->uuid;
            $questionnaire->taskType = $row->task_type;
            $questionnaire->questions = [];
            $list->questionnaires[] = $questionnaire;
        }

        return $list;
    }
 }
