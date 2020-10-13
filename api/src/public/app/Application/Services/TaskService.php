<?php
namespace App\Application\Services;

use App\Application\Models\CaseTaskList;
use App\Application\Models\GeneralTaskList;
use App\Application\Repositories\CaseTaskRepository;
use App\Application\Repositories\GeneralTaskRepository;
use Exception;
use Psr\Log\LoggerInterface;

/**
 * Responsible for listing tasks.
 *
 * @package App\Application\Services
 */
class TaskService
{
    /**
     * @var GeneralTaskRepository
     */
    private GeneralTaskRepository $generalTaskRepository;

    /**
     * @var CaseTaskRepository
     */
    private CaseTaskRepository $caseTaskRepository;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * Constructor.
     *
     * @param GeneralTaskRepository $generalTaskRepository
     * @param CaseTaskRepository    $caseTaskRepository
     * @param LoggerInterface       $logger
     */
    public function __construct(
        GeneralTaskRepository $generalTaskRepository,
        CaseTaskRepository $caseTaskRepository,
        LoggerInterface $logger
    )
    {
        $this->generalTaskRepository = $generalTaskRepository;
        $this->caseTaskRepository = $caseTaskRepository;
        $this->logger = $logger;
    }

    /**
     * Returns the general task list.
     *
     * @return GeneralTaskList
     *
     * @throws Exception
     */
    public function getGeneralTasks(): GeneralTaskList
    {
        return $this->generalTaskRepository->getGeneralTasks();
    }

    /**
     * Returns the case task list.
     *
     * @param string $caseId Case identifier.
     *
     * @return CaseTaskList
     */
    public function getCaseTasks(string $caseId): CaseTaskList
    {
        // TODO: verify access to case using signed otp
        return $this->caseTaskRepository->getCaseTasks($caseId);
    }

    /**
     * Submit case tasks.
     *
     * @param string $caseId Case identifier.
     * @param string $body   Encrypted body.
     */
    public function submitCaseTasks(string $caseId, string $body): void
    {
        // TODO: verify signature and access
        $this->caseTaskRepository->submitCaseTasks($caseId, $body);
    }
}
