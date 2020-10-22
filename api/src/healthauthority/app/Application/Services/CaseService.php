<?php
namespace DBCO\HealthAuthorityAPI\Application\Services;

use DBCO\HealthAuthorityAPI\Application\Models\CovidCase;
use DBCO\HealthAuthorityAPI\Application\Models\TaskList;
use DBCO\HealthAuthorityAPI\Application\Repositories\CaseRepository;
use DBCO\HealthAuthorityAPI\Application\Repositories\GeneralTaskRepository;
use Exception;
use Psr\Log\LoggerInterface;

/**
 * Responsible for listing tasks.
 *
 * @package DBCO\HealthAuthorityAPI\Application\Services
 */
class CaseService
{
    /**
     * @var GeneralTaskRepository
     */
    private GeneralTaskRepository $generalTaskRepository;

    /**
     * @var CaseRepository
     */
    private CaseRepository $caseRepository;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * Constructor.
     *
     * @param GeneralTaskRepository $generalTaskRepository
     * @param CaseRepository        $caseRepository
     * @param LoggerInterface       $logger
     */
    public function __construct(
        GeneralTaskRepository $generalTaskRepository,
        CaseRepository $caseRepository,
        LoggerInterface $logger
    )
    {
        $this->generalTaskRepository = $generalTaskRepository;
        $this->caseRepository = $caseRepository;
        $this->logger = $logger;
    }

    /**
     * Returns the general task list.
     *
     * @return TaskList
     *
     * @throws Exception
     */
    public function getGeneralTasks(): TaskList
    {
        return $this->generalTaskRepository->getGeneralTasks();
    }

    /**
     * Returns the case with task list.
     *
     * @param string $caseId Case identifier.
     *
     * @return CovidCase
     */
    public function getCase(string $caseId): CovidCase
    {
        return $this->caseRepository->getCase($caseId);
    }

    /**
     * Submit case with tasks.
     *
     * @param string $caseId Case identifier.
     * @param string $body   Encrypted body.
     */
    public function submitCase(string $caseId, string $body): void
    {
        // TODO: decrypt
        $this->caseRepository->submitCase($caseId, $body);
    }
}
