<?php
namespace DBCO\PublicAPI\Application\Services;

use DBCO\PublicAPI\Application\Models\QuestionnaireList;
use DBCO\PublicAPI\Application\Repositories\QuestionnaireRepository;
use Exception;
use Psr\Log\LoggerInterface;

/**
 * Responsible for listing questionnaires.
 *
 * @package DBCO\PublicAPI\Application\Services
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
     * @param string $language Language.
     *
     * @return QuestionnaireList
     *
     * @throws Exception
     */
    public function getQuestionnaires(string $language): QuestionnaireList
    {
        return $this->questionnaireRepository->getQuestionnaires($language);
    }
}
