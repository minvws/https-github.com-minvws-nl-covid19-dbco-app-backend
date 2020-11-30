<?php
namespace DBCO\PublicAPI\Application\Services;

use DBCO\PublicAPI\Application\Models\SealedCase;
use DBCO\PublicAPI\Application\Models\GeneralTaskList;
use DBCO\PublicAPI\Application\Repositories\CaseRepository;
use DBCO\PublicAPI\Application\Repositories\CaseSubmitRepository;
use DBCO\PublicAPI\Application\Repositories\GeneralTaskRepository;
use DBCO\Shared\Application\Models\SealedData;
use Exception;
use Psr\Log\LoggerInterface;

/**
 * Responsible for listing tasks.
 *
 * @package DBCO\PublicAPI\Application\Services
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
     * @var CaseSubmitRepository
     */
    private CaseSubmitRepository $caseSubmitRepository;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * Constructor.
     *
     * @param GeneralTaskRepository $generalTaskRepository
     * @param CaseRepository        $caseRepository
     * @param CaseSubmitRepository  $caseSubmitRepository
     * @param LoggerInterface       $logger
     */
    public function __construct(
        GeneralTaskRepository $generalTaskRepository,
        CaseRepository $caseRepository,
        CaseSubmitRepository $caseSubmitRepository,
        LoggerInterface $logger
    )
    {
        $this->generalTaskRepository = $generalTaskRepository;
        $this->caseRepository = $caseRepository;
        $this->caseSubmitRepository = $caseSubmitRepository;
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
     * Returns the case and its task list.
     *
     * @param string $token Case token.
     *
     * @return SealedData
     */
    public function getCase(string $token): ?SealedData
    {
        return $this->caseRepository->getCase($token);
    }

    /**
     * Submit case tasks.
     *
     * @param string     $token      Case token.
     * @param SealedData $sealedCase Sealed case.
     */
    public function submitCase(string $token, SealedData $sealedCase): bool
    {
        if (!$this->caseRepository->caseExists($token)) {
            return false;
        }

        $this->caseSubmitRepository->submitCase($token, $sealedCase);

        return true;
    }
}
