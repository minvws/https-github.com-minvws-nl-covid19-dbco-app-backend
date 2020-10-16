<?php
namespace App\Application\Services;

use App\Application\Models\QuestionnaireList;
use App\Application\Repositories\QuestionnaireCacheRepository;
use App\Application\Repositories\QuestionnaireGetRepository;
use Exception;
use Psr\Log\LoggerInterface;

class QuestionnaireService
{
    /**
     * @var QuestionnaireGetRepository
     */
    private QuestionnaireGetRepository $questionnaireGetRepository;

    /**
     * @var QuestionnaireCacheRepository
     */
    private QuestionnaireCacheRepository $questionnaireCacheRepository;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * Constructor.
     *
     * @param QuestionnaireGetRepository   $questionnaireGetRepository
     * @param QuestionnaireCacheRepository $questionnaireCacheRepository
     * @param LoggerInterface              $logger
     */
    public function __construct(
        QuestionnaireGetRepository $questionnaireGetRepository,
        QuestionnaireCacheRepository $questionnaireCacheRepository,
        LoggerInterface $logger
    )
    {
        $this->questionnaireGetRepository = $questionnaireGetRepository;
        $this->questionnaireCacheRepository = $questionnaireCacheRepository;
        $this->logger = $logger;
    }

    /**
     * Retrieve a fresh list of questionnaires.
     *
     * @return QuestionnaireList
     *
     * @throws Exception
     */
    private function getQuestionnaires(): QuestionnaireList
    {
        return $this->questionnaireGetRepository->getQuestionnaires();
    }

    /**
     * Store the questionnaire list in the cache.
     *
     * @param QuestionnaireList $questionnaires
     *
     * @throws Exception
     */
    private function cacheQuestionnaires(QuestionnaireList $questionnaires)
    {
        $this->questionnaireCacheRepository->putQuestionnaires($questionnaires);
    }

    /**
     * Refresh the questionnaire cache.
     *
     * @throws Exception
     */
    public function refreshQuestionnaires()
    {
        $this->logger->debug('Refreshing questionnaire cache');

        try {
            $questionnaires = $this->getQuestionnaires();
            $this->cacheQuestionnaires($questionnaires);

            $this->logger->debug('Successfully refreshed questionnaire cache');
        } catch (Exception $e) {
            $this->logger->error('Error refreshing questionnaire cache: ' . $e->getMessage());
            $this->logger->debug($e->getTraceAsString());
            throw $e;
        }
    }
}
