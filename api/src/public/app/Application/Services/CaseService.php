<?php
namespace DBCO\PublicAPI\Application\Services;

use DBCO\PublicAPI\Application\Models\SealedCase;
use DBCO\PublicAPI\Application\Models\GeneralTaskList;
use DBCO\PublicAPI\Application\Repositories\CaseRepository;
use DBCO\PublicAPI\Application\Repositories\GeneralTaskRepository;
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
     * @param string $caseId Case identifier.
     *
     * @return SealedCase
     */
    public function getCase(string $caseId): SealedCase
    {
        // TODO: verify access to case using signed otp
        return $this->caseRepository->getCase($caseId);
    }

    /**
     * Submit case tasks.
     *
     * @param string $token       Case token.
     * @param string $ciphertext  Sealed case ciphertext.
     * @param string $nonce       Sealed case nonce.
     */
    public function submitCase(string $token, string $ciphertext, string $nonce): void
    {
        // TODO: verify access
        $sealedCase = new SealedCase($ciphertext, $nonce);
        $this->caseRepository->submitCase($token, $sealedCase);
    }
}
