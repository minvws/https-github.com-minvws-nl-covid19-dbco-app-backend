<?php
namespace DBCO\HealthAuthorityAPI\Application\Services;

use DBCO\HealthAuthorityAPI\Application\Models\QuestionnaireList;
use DBCO\HealthAuthorityAPI\Application\Repositories\QuestionnaireRepository;
use Exception;
use Psr\Log\LoggerInterface;

/**
 * Responsible for listing questionnaires.
 *
 * @package DBCO\HealthAuthorityAPI\Application\Services
 */
class QuestionnaireService
{
    /**
     * @var QuestionnaireRepository
     */
    private QuestionnaireRepository $questionnaireRepository;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * Constructor.
     *
     * @param QuestionnaireRepository $questionnaireRepository
     * @param LoggerInterface         $logger
     */
    public function __construct(
        QuestionnaireRepository $questionnaireRepository,
        LoggerInterface $logger
    )
    {
        $this->questionnaireRepository = $questionnaireRepository;
        $this->logger = $logger;
    }

    /**
     * Returns the questionnaire list.
     *
     * @return QuestionnaireList
     *
     * @throws Exception
     */
    public function getQuestionnaires(): QuestionnaireList
    {
        return $this->questionnaireRepository->getQuestionnaires();
    }
}
