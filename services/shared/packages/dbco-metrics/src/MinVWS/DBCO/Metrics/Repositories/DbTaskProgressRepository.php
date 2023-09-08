<?php

namespace MinVWS\DBCO\Metrics\Repositories;

use MinVWS\DBCO\Encryption\Security\CacheEntryNotFoundException;
use MinVWS\DBCO\Encryption\Security\EncryptionHelper;
use PDO;

/**
 * Store events and exports in database.
 *
 * @package MinVWS\Metrics\Repositories
 */
class DbTaskProgressRepository implements TaskProgressRepository
{
    /**
     * @var PDO
     */
    protected PDO $client;

    /**
     * @var \MinVWS\DBCO\Encryption\Security\EncryptionHelper
     */
    protected EncryptionHelper $encryptionHelper;

    /**
     * Constructor.
     */
    public function __construct(PDO $client, EncryptionHelper $encryptionHelper)
    {
        $this->client = $client;
        $this->encryptionHelper = $encryptionHelper;
    }

    /**
     * Retrieve all data need to determine the progress of a task.
     *
     * @param string $taskUuid
     * @return array
     */
    public function getTaskData(string $taskUuid): array
    {
        $stmt = $this->client->prepare("
            SELECT
                t.date_of_last_exposure,
                t.category,
                t.general,
                q.question_type,
                q.relevant_for_categories,
                a.ctd_firstname,
                a.ctd_lastname,
                a.ctd_phonenumber,
                a.ctd_email,
                a.spv_value,
                a.cfd_cat_1_risk,
                a.cfd_cat_2a_risk,
                a.cfd_cat_2b_risk,
                a.cfd_cat_3_risk
            FROM task t 
            LEFT JOIN question q ON q.questionnaire_uuid = t.questionnaire_uuid
            LEFT JOIN answer a ON a.question_uuid = q.uuid AND a.task_uuid = t.uuid
            WHERE t.uuid = :taskUuid
        ");

        $stmt->execute(['taskUuid' => $taskUuid]);

        $dbQuestionsAndAnswers = $stmt->fetchAll(PDO::FETCH_OBJ);
        if (!$dbQuestionsAndAnswers) {
            return [];
        }
        $taskData = [];
        $taskData['category'] = $dbQuestionsAndAnswers[0]->category;
        $taskData['date_of_last_exposure'] = $dbQuestionsAndAnswers[0]->date_of_last_exposure;
        $taskData['general'] = !empty($dbQuestionsAndAnswers[0]->general) ? json_decode($this->encryptionHelper->unsealOptionalStoreValue($dbQuestionsAndAnswers[0]->general), true) : null;

        foreach ($dbQuestionsAndAnswers as $questionsAndAnswer) {
            try {
                $question = [
                    'question_type' => $questionsAndAnswer->question_type,
                    'relevant_for_categories' => explode(',', $questionsAndAnswer->relevant_for_categories),
                    'ctd_firstname' => $this->encryptionHelper->unsealOptionalStoreValue($questionsAndAnswer->ctd_firstname),
                    'ctd_lastname' => $this->encryptionHelper->unsealOptionalStoreValue($questionsAndAnswer->ctd_lastname),
                    'ctd_phonenumber' => $this->encryptionHelper->unsealOptionalStoreValue($questionsAndAnswer->ctd_phonenumber),
                    'ctd_email' => $this->encryptionHelper->unsealOptionalStoreValue($questionsAndAnswer->ctd_email),
                    'spv_value' => $this->encryptionHelper->unsealOptionalStoreValue($questionsAndAnswer->spv_value),
                    'cfd_cat_1_risk' => $questionsAndAnswer->cfd_cat_1_risk,
                    'cfd_cat_2a_risk' => $questionsAndAnswer->cfd_cat_2a_risk,
                    'cfd_cat_2b_risk' => $questionsAndAnswer->cfd_cat_2b_risk,
                    'cfd_cat_3_risk' => $questionsAndAnswer->cfd_cat_3_risk
                ] ;
            } catch (CacheEntryNotFoundException $e) {
                $question = [] ;
            }
            $taskData['questions'][] = $question;
        }
        return $taskData;
    }
}
